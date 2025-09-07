# app.py
import os  # 需要导入 os 来获取环境变量
from PIL import Image
import pandas as pd
import onnxruntime as rt
import numpy as np
import argparse
import io
import gradio as gr
import requests

# --- 新增：导入智谱AI SDK ---
try:
    from zhipuai import ZhipuAI
    ZHIPUAI_AVAILABLE = True
except ImportError:
    print("警告：未安装 zhipuai 库。智谱AI功能将不可用。请运行 'pip install zhipuai' 来安装。")
    ZHIPUAI_AVAILABLE = False

# --- 固定设置，仅使用本地模型 ---
# 指定本地模型文件的绝对路径
LOCAL_MODEL_DIR = "/Volumes/AI/AI/wd-tagger/model" # 指向包含 model.onnx 和 selected_tags.csv 的目录
MODEL_PATH = os.path.join(LOCAL_MODEL_DIR, "model.onnx")
CSV_PATH = os.path.join(LOCAL_MODEL_DIR, "selected_tags.csv")
# --- 新增：指定中文翻译文件路径 ---
CSV_ZH_PATH = os.path.join(LOCAL_MODEL_DIR, "selected_tags_zh_CN.csv")
# 固定使用的模型名称（仅用于显示）
MODEL_REPO = "fireicewolf/wd-swinv2-tagger-v3"
TITLE = "AI 打标器 v.1.3.1-智谱AI"
DESCRIPTION = """
使用本地 `wd-swinv2-tagger-v3` 模型进行图像标签预测。
"""
# Dataset v3 series of models:
# SWINV2_MODEL_DSV3_REPO = "fireicewolf/wd-swinv2-tagger-v3"
# ... 其他模型定义已注释掉 ...
# Dataset v2 series of models:
# ... 其他模型定义已注释掉 ...
# Files to download from the repos
# MODEL_FILENAME = "model.onnx"
# LABEL_FILENAME = "selected_tags.csv"
# https://github.com/toriato/stable-diffusion-webui-wd14-tagger/blob/a9eacb1eff904552d3012babfa28b57e1d3e295c/tagger/ui.py#L368
kaomojis = [
    "0_0",
    "(o)_(o)",
    "+_+",
    "+_-",
    "._.",
    "<o>_<o>",
    "<|>_<|>",
    "=_=",
    ">_<",
    "3_3",
    "6_9",
    ">_o",
    "@_@",
    "^_^",
    "o_o",
    "u_u",
    "x_x",
    "|_|",
    "||_||",
]

# --- 移除或注释掉与加密相关的函数，因为我们使用的是原始CSV ---
# def xor_cipher(input_bytes, key=170):
#     ...
# def encrypt_xor(file_path, key):
#     ...
# def decrypt_and_load_csv(file_path, key):
#     ...

def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument("--score-slider-step", type=float, default=0.05)
    parser.add_argument("--score-general-threshold", type=float, default=0.2) # 保持与上次一致
    parser.add_argument("--score-character-threshold",
                        type=float, default=0.85)
    parser.add_argument("--share", action="store_true")
    return parser.parse_args()

def load_labels(dataframe) -> list[str]:
    name_series = dataframe["name"].fillna('').astype(str)
    # print(name_series.head(10)) # 可选：调试输出
    name_series = name_series.map(
        lambda x: x.replace("_", " ") if x not in kaomojis else x
    )
    tag_names = name_series.tolist()
    rating_indexes = list(np.where(dataframe["category"] == 9)[0])
    general_indexes = list(np.where(dataframe["category"] == 0)[0])
    character_indexes = list(np.where(dataframe["category"] == 4)[0])
    return tag_names, rating_indexes, general_indexes, character_indexes

# --- 新增：辅助函数，用于标准化 key ---
def _standardize_key(tag_key: str) -> str:
    """将标签 key 标准化为统一格式，例如将空格替换为下划线"""
    if not isinstance(tag_key, str):
        return tag_key
    return tag_key.replace(" ", "_") # 统一使用下划线

# --- 修改：加载中文翻译字典 ---
def load_chinese_translations(csv_path):
    """加载英文标签到中文翻译的映射字典，并标准化 key"""
    if not os.path.exists(csv_path):
        print(f"警告：找不到中文翻译文件 {csv_path}，将不提供中文翻译。")
        return {}
    try:
        df = pd.read_csv(csv_path)
        # 假设列名是 'key' 和 'lang'
        # 在加载时就将 key 标准化
        df['standardized_key'] = df['key'].apply(_standardize_key)
        # 使用标准化后的 key 构建字典
        translation_dict = dict(zip(df['standardized_key'], df['lang']))
        print(f"已加载 {len(translation_dict)} 条中文翻译 (key 已标准化)。")
        return translation_dict
    except Exception as e:
        print(f"加载中文翻译文件 {csv_path} 时出错: {e}")
        return {}

def mcut_threshold(probs):
    """
    Maximum Cut Thresholding (MCut)
    Largeron, C., Moulin, C., & Gery, M. (2012). MCut: A Thresholding Strategy
     for Multi-label Classification. In 11th International Symposium, IDA 2012
     (pp. 172-183).
    """
    sorted_probs = probs[probs.argsort()[::-1]]
    difs = sorted_probs[:-1] - sorted_probs[1:]
    t = difs.argmax()
    thresh = (sorted_probs[t] + sorted_probs[t + 1]) / 2
    return thresh

# --- 新增：提取标签的辅助函数 ---
def extract_tags_from_text(text: str):
    """
    从智谱AI返回的文本中提取英文和中文标签
    """
    import re
    
    # 提取英文标签 []
    english_tags = re.findall(r'\[([^\]]+)\]', text)
    english_tags_str = ', '.join(english_tags) if english_tags else ""
    
    # 提取中文标签 【】
    chinese_tags = re.findall(r'【([^】]+)】', text)
    chinese_tags_str = ', '.join(chinese_tags) if chinese_tags else ""
    
    return english_tags_str, chinese_tags_str

# --- 新增：调用智谱AI API 的函数 ---
def call_zhipuai_api(image_url: str):
    """
    调用智谱AI GLM-4.1V-Thinking API 获取图片描述，并返回结果和提取的标签。
    返回: (api_result, english_tags, chinese_tags, image_for_display)
    """
    if not ZHIPUAI_AVAILABLE:
        return "错误：zhipuai 库未安装。", "", "", None

    api_key = os.getenv("ZHIPUAI_API_KEY")
    if not api_key:
        return "错误：未设置环境变量 ZHIPUAI_API_KEY。", "", "", None

    # 功能1：加载图片用于显示
    image_for_display = None
    try:
        import requests
        from PIL import Image
        response = requests.get(image_url)
        if response.status_code == 200:
            image_for_display = Image.open(io.BytesIO(response.content))
    except Exception as e:
        print(f"加载图片用于显示时出错: {e}")

    try:
        client = ZhipuAI(api_key=api_key) # 使用环境变量中的 API Key
        print(f"正在调用智谱AI API 处理图片 URL: {image_url}")
        response = client.chat.completions.create(
            model="glm-4.5v",  # 使用指定模型
            messages=[
                {
                    "role": "user",
                    "content": [
                        {
                            "type": "text",
                            "text": "请提供该图片的中英文详细描述，这些描述信息将用于AI绘画的prompt，中文描述用两个五角星★★符号包围，英文描述用两个方块■■符号包围。最后再将中英文的prompt描述内容简化成tag标签。中文Tag标签用中文大写书括号【】符号包围，英文Tag标签用英文小写书括号[]符号包围，中英文的每个tag标签在包围符号内用英文逗号分割。最后返回的四段信息分别是：中文详细信息（用于AI绘画的Prompt）、 英文详细信息（用于AI绘画的Prompt）、中文Tag标签、英文Tag标签。"
                        },
                        {
                            "type": "image_url",
                            "image_url": {
                                "url": image_url
                            }
                        }
                    ]
                }
            ]
        )
        # 提取并返回模型的回复内容
        result_text = response.choices[0].message.content
        print("智谱AI API 调用成功。")
        
        # 功能2和3：提取标签
        english_tags, chinese_tags = extract_tags_from_text(result_text)
        
        return result_text, english_tags, chinese_tags, image_for_display
    except Exception as e:
        error_msg = f"调用智谱AI API 时出错: {e}"
        print(error_msg)
        return error_msg, "", "", image_for_display

class Predictor:
    def __init__(self):
        self.model_target_size = None
        self.last_loaded_repo = None # 用于跟踪是否已加载模型
        self.tag_names = None
        self.rating_indexes = None
        self.general_indexes = None
        self.character_indexes = None
        self.model = None
        # --- 新增：存储中文翻译字典 ---
        self.chinese_translations = {}

    # --- 修改 download_model 方法 ---
    # 由于使用本地文件，此方法不再需要下载，直接返回本地路径
    def download_model(self, model_repo):
        # 直接返回本地文件路径
        # 注意：model_repo 参数在此实现中未被使用，因为我们只加载一个固定的本地模型
        print(f"使用本地模型文件: {MODEL_PATH}, {CSV_PATH}")
        return CSV_PATH, MODEL_PATH

    # --- 修改 load_model 方法 ---
    def load_model(self, model_repo):
        # 检查模型是否已经加载过了（基于 model_repo）
        # 注意：这里我们用 model_repo 作为标识符，虽然实际加载的是本地文件
        if model_repo == self.last_loaded_repo and self.model is not None:
            print("已加载的模型.")
            return # 如果已经加载，则直接返回
        print(f"从本地文件加载模型以进行存储库: {model_repo}")
        csv_path, model_path = self.download_model(model_repo)
        # 加载标签 CSV 文件
        tags_df = pd.read_csv(csv_path) # 直接读取本地CSV
        sep_tags = load_labels(tags_df)
        self.tag_names = sep_tags[0]
        self.rating_indexes = sep_tags[1]
        self.general_indexes = sep_tags[2]
        self.character_indexes = sep_tags[3]
        # --- 新增：加载中文翻译 ---
        self.chinese_translations = load_chinese_translations(CSV_ZH_PATH)
        # 加载 ONNX 模型
        self.model = rt.InferenceSession(model_path) # 从本地路径加载模型
        _, height, width, _ = self.model.get_inputs()[0].shape
        self.model_target_size = height
        self.last_loaded_repo = model_repo # 标记模型已加载
        print("模型加载成功.")

    def prepare_image(self, image):
        target_size = self.model_target_size
        # 将RGBA图像转换为RGB，背景填充为白色
        canvas = Image.new("RGBA", image.size, (255, 255, 255))
        canvas.alpha_composite(image)
        image = canvas.convert("RGB")
        # 将图像填充为正方形
        image_shape = image.size
        max_dim = max(image_shape)
        pad_left = (max_dim - image_shape[0]) // 2
        pad_top = (max_dim - image_shape[1]) // 2
        padded_image = Image.new("RGB", (max_dim, max_dim), (255, 255, 255))
        padded_image.paste(image, (pad_left, pad_top))
        # 调整大小
        if max_dim != target_size:
            padded_image = padded_image.resize(
                (target_size, target_size),
                Image.BICUBIC, # 或 Image.LANCZOS
            )
        # 转换为 numpy 数组
        image_array = np.asarray(padded_image, dtype=np.float32)
        # 将 PIL 原生的 RGB 转换为 ONNX 模型期望的 BGR 格式
        image_array = image_array[:, :, ::-1]
        # 添加批次维度 (1, H, W, C)
        return np.expand_dims(image_array, axis=0)

    def predict(
        self,
        image,
        model_repo, # 这个参数仍然保留，但内部实现会忽略它，因为我们只用本地模型
        general_thresh,
        general_mcut_enabled,
        character_thresh,
        character_mcut_enabled,
    ):
        # 加载模型（如果尚未加载）
        self.load_model(model_repo)
        # 预处理图像
        if image is not None: # 检查图像是否存在
            image = self.prepare_image(image)
            # 运行模型推理
            input_name = self.model.get_inputs()[0].name
            label_name = self.model.get_outputs()[0].name
            preds = self.model.run([label_name], {input_name: image})[0]
            # 将预测结果与标签名称配对
            labels = list(zip(self.tag_names, preds[0].astype(float)))
            # 处理评级标签 (Rating)
            ratings_names = [labels[i] for i in self.rating_indexes]
            rating = dict(ratings_names)
            # 处理通用标签 (General Tags)
            general_names = [labels[i] for i in self.general_indexes]
            if general_mcut_enabled:
                general_probs = np.array([x[1] for x in general_names])
                general_thresh = mcut_threshold(general_probs)
            general_res = [x for x in general_names if x[1] > general_thresh]
            general_res = dict(general_res)
            # 处理角色标签 (Character Tags)
            character_names = [labels[i] for i in self.character_indexes]
            if character_mcut_enabled:
                character_probs = np.array([x[1] for x in character_names])
                character_thresh = mcut_threshold(character_probs)
                character_thresh = max(0.15, character_thresh) # 确保最小阈值
            character_res = [x for x in character_names if x[1] > character_thresh]
            character_res = dict(character_res)
            # 对通用标签按置信度降序排序，并格式化为字符串
            sorted_general_strings = sorted(
                general_res.items(),
                key=lambda x: x[1],
                reverse=True,
            )
            sorted_general_strings = [x[0] for x in sorted_general_strings]
            # 转义括号，防止在某些显示环境中出现问题
            sorted_general_strings_output = (
                ", ".join(sorted_general_strings)
                .replace("(", "\\(")
                .replace(")", "\\)")
            )

            # --- 修改：生成中文翻译字符串 ---
            # 在查找翻译时也标准化 key
            sorted_general_strings_zh = [
                self.chinese_translations.get(_standardize_key(tag), tag) for tag in sorted_general_strings
            ]
            sorted_general_strings_zh_output = ", ".join(sorted_general_strings_zh)
        else:
             # 如果没有图像，则返回空结果
            sorted_general_strings_output = ""
            sorted_general_strings_zh_output = ""
            rating = {}
            character_res = {}
            general_res = {}

        # 返回结果 (注意：增加了 sorted_general_strings_zh_output)
        # 返回空字符串占位符，API结果将由单独的函数处理
        return sorted_general_strings_output, sorted_general_strings_zh_output, rating, character_res, general_res, ""

def main():
    args = parse_args()
    predictor = Predictor()
    # --- 简化下拉列表，只包含一个选项 ---
    dropdown_list = [MODEL_REPO] # 只有一个选项

    with gr.Blocks(title=TITLE) as demo:
        with gr.Column():
            gr.Markdown(
                value=f"<h1 style='text-align: center; margin-bottom: 1rem'>{TITLE}</h1>"
            )
            gr.Markdown(value=DESCRIPTION)
            with gr.Row():
                with gr.Column(variant="panel"):
                    # --- 新增：智谱AI API 调用 UI ---
                    with gr.Row():
                        image_url_input = gr.Textbox(
                            label="图片 URL (用于智谱AI)",
                            placeholder="请输入图片的完整URL..."
                        )

                    # --- 调整 UI：将按钮移到图像上方 ---
                    with gr.Row(): # 使用 Row 来并排放置按钮
                         # 清除按钮现在只清除图像
                        clear_btn = gr.ClearButton(
                            components=[], # 初始为空，稍后添加 image
                            value="清除图像 Clear",
                            variant="secondary",
                            size="sm", # 使用较小的按钮
                        )
                        submit = gr.Button(
                            value="本地模型打Tag标签", variant="primary", size="sm") # 使用较小的按钮

                        zhipuai_btn = gr.Button(
                            value="智谱AI 图片反推", variant="secondary", size="sm"
                        )

                    image = gr.Image(
                        type="pil", image_mode="RGBA", label="输入图像")

                    # --- 下拉列表简化为只读显示 ---
                    model_repo = gr.Dropdown(
                        choices=dropdown_list, # 固定选项
                        value=MODEL_REPO,       # 固定默认值
                        label="Model 参考模型",
                        interactive=False     # 禁止用户更改
                    )
                    with gr.Row():
                        general_thresh = gr.Slider(
                            minimum=0,
                            maximum=1,
                            step=args.score_slider_step,
                            value=args.score_general_threshold,
                            label="常规标记阈值",
                            # scale=3, # Gradio 旧版本可能不支持 scale
                        )
                        general_mcut_enabled = gr.Checkbox(
                            value=False,
                            label="对常规标签使用 MCut 阈值",
                            # scale=1,
                        )
                    with gr.Row():
                        character_thresh = gr.Slider(
                            minimum=0,
                            maximum=1,
                            step=args.score_slider_step,
                            value=args.score_character_threshold,
                            label="字符标签阈值",
                            # scale=3,
                        )
                        character_mcut_enabled = gr.Checkbox(
                            value=False,
                            label="对角色标签使用MCut阈值",
                            # scale=1,
                        )

                # --- 在这一列定义输出组件 ---
                with gr.Column(variant="panel"):
                    sorted_general_strings = gr.Textbox(
                        label="Tag标签（字符串）", interactive=True)
                    # --- 新增：中文翻译文本框 ---
                    sorted_general_strings_zh = gr.Textbox(
                        label="中文翻译", interactive=True)
                    # --- 新增：智谱AI API 结果文本框 ---
                    zhipuai_result = gr.Textbox(
                        label="图片反推数据", lines=10, max_lines=20, interactive=True) # 可设置为可编辑
                    general_res = gr.Label(label="Tag标签（Dict）")
                    rating = gr.Label(label="预测评级")
                    character_res = gr.Label(label="预测字符标签")

            # --- 关键修改：在定义了所有要清除的组件之后，再调用 clear_btn.add ---
            # 将清除按钮的目标组件添加到 ClearButton
            # --- 更新：包含新的中文翻译框和智谱AI结果框 ---
            # 注意：clear_btn 现在只清除 image，其他输出由 submit.click 和 zhipuai_btn.click 清除或更新
            clear_btn.add([image]) # 只清除图像

        # --- 连接按钮事件 ---
        # --- 更新：outputs 列表包含新的中文翻译输出和占位符 ---
        submit.click(
            fn=predictor.predict,
            inputs=[
                image,
                model_repo,
                general_thresh,
                general_mcut_enabled,
                character_thresh,
                character_mcut_enabled,
            ],
            outputs=[sorted_general_strings, sorted_general_strings_zh, rating, character_res, general_res, zhipuai_result], # 添加 zhipuai_result 作为输出占位符
            concurrency_limit=1 # 限制并发，避免同时加载模型
        )

        # --- 新增：连接智谱AI按钮事件 ---
        if ZHIPUAI_AVAILABLE:
            zhipuai_btn.click(
                fn=call_zhipuai_api,
                inputs=image_url_input,
                outputs=[zhipuai_result, sorted_general_strings, sorted_general_strings_zh, image],
                concurrency_limit=1 # 限制并发
            )
        else:
             # 如果库不可用，禁用按钮或显示错误
            zhipuai_btn.click(
                fn=lambda: ("错误：zhipuai 库未安装。", "", "", None),
                inputs=None,
                outputs=[zhipuai_result, sorted_general_strings, sorted_general_strings_zh, image]
            )

        # --- 示例 ---
        # gr.Examples(
        #     examples=[
        #         [os.path.join(os.path.dirname(__file__), "power.jpg")] # 假设 power.jpg 在脚本同目录
        #          # 其他参数使用默认值即可，因为模型和阈值是固定的或有默认值
        #     ],
        #     inputs=[image], # 只需要提供图像输入示例
        #     # outputs 和 fn 会自动关联到 submit.click
        # )

    # --- 启动 Gradio 应用 ---
    demo.queue(max_size=10) # 启用队列处理请求
    demo.launch(share=args.share, server_port=8088, inbrowser=True) # 根据命令行参数决定是否共享

if __name__ == "__main__":
    main()
