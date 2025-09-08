HTML/JavaScript 是在客户端（浏览器）执行的。两者需要通过 HTTP 请求（如表单提交或 AJAX）进行通信。

以下是为您重新设计的、结构正确且功能完整的解决方案。它包含两个核心文件：

index.php: 前端界面和 JavaScript 逻辑，负责收集用户输入、上传文件，并通过 AJAX 将数据发送给后端。

process.php: 后端 PHP 逻辑，负责接收文件、调用智谱AI API、创建文件夹、重命名文件并保存 TXT 结果。

## API 接口文档

curl -X POST \
  https://open.bigmodel.cn/api/paas/v4/chat/completions \
  -H "Authorization: Bearer your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "glm-4.5v",
    "messages": [
      {
        "role": "user",
        "content": [
          {
            "type": "image_url",
            "image_url": {
              "url": "https://cloudcovert-1305175928.cos.ap-guangzhou.myqcloud.com/%E5%9B%BE%E7%89%87grounding.PNG"
            }
          },
          {
            "type": "text",
            "text": "你是一个AI绘画提示词专家。请根据我提供的图片进行文字描述，形成用于AI绘画的一段非常丰富的中英文画面详细描述，这些描述信息将重新用于AI绘画的prompt，并且另将中英文的prompt描述内容分别简化成对应的tag标签。最后返回的是一个 json 的数组数据信息， json 里面包含四段信息分别是：cn（用于AI绘画的中文详细自然语言信息Prompt）、en （用于AI绘画的英文详细信息Prompt）、cn-tag（中文Tag标签用中文逗号内部区分）、en-tag(英文Tag标签用英文逗号内部隔开)。不需要输出无用markdown语言注释信息，直接输出 json 格式的数据。"
          }
        ]
      }
    ],
    "thinking": {
      "type":"enabled"
    }
  }'

## 返回的信息：

<|begin_of_box|>{"cn":"一位拥有短款渐变色头发的女性，发色从暖棕色过渡到紫色与蓝色，发丝轻盈飘逸；她身着白色褶皱领口的上衣，佩戴精致珍珠耳环与细金项链；背景是柔和的水彩风格，融合了淡蓝色天空、浅绿色草地与朦胧的光影，整体氛围清新梦幻，光线明亮温暖。","en":"A woman with short gradient hair, transitioning from warm brown to purple and blue, her hair strands light and flowing; she wears a white blouse with pleated neckline, delicate pearl earrings, and a thin gold necklace; the background is in soft watercolor style, blending pale blue sky, light green grass, and hazy light and shadow, creating a fresh and dreamy atmosphere with bright and warm lighting.","cn-tag":"短款渐变色头发,白色褶皱上衣,珍珠耳环,细金项链,水彩背景,清新梦幻","en-tag":"short gradient hair, white pleated blouse, pearl earrings, thin gold necklace, watercolor background, fresh dreamy"}<|end_of_box|>


系统应该能够：

  1. 中文描述：显示 cn 字段的内容
  2. 英文描述：显示 en 字段的内容
  3. 中文标签：将 cn-tag 字段按中文逗号（，）分割并显示为标签
  4. 英文标签：将 en-tag 字段按英文逗号（,）分割并显示为标签