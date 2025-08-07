import safetensors.torch
from safetensors import safe_open
import torch
import sys
import os

def patch_final_layer_adaLN(state_dict, prefix="lora_unet_final_layer", verbose=True):
    """
    Add dummy adaLN weights if missing, using final_layer_linear shapes as reference.
    Args:
        state_dict (dict): keys -> tensors
        prefix (str): base name for final_layer keys
        verbose (bool): print debug info
    Returns:
        dict: patched state_dict
    """
    final_layer_linear_down = None
    final_layer_linear_up = None

    adaLN_down_key = f"{prefix}_adaLN_modulation_1.lora_down.weight"
    adaLN_up_key = f"{prefix}_adaLN_modulation_1.lora_up.weight"
    linear_down_key = f"{prefix}_linear.lora_down.weight"
    linear_up_key = f"{prefix}_linear.lora_up.weight"

    if verbose:
        print(f"\n🔍 Checking for final_layer keys with prefix: '{prefix}'")
        print(f"   Linear down: {linear_down_key}")
        print(f"   Linear up:   {linear_up_key}")

    if linear_down_key in state_dict:
        final_layer_linear_down = state_dict[linear_down_key]
    if linear_up_key in state_dict:
        final_layer_linear_up = state_dict[linear_up_key]

    has_adaLN = adaLN_down_key in state_dict and adaLN_up_key in state_dict
    has_linear = final_layer_linear_down is not None and final_layer_linear_up is not None

    if verbose:
        print(f"   ✅ Has final_layer.linear: {has_linear}")
        print(f"   ✅ Has final_layer.adaLN_modulation_1: {has_adaLN}")

    if has_linear and not has_adaLN:
        # 创建与linear权重相同形状的adaLN权重
        # 使用小的非零值而不是完全为零，以确保权重被正确识别
        dummy_down = torch.randn_like(final_layer_linear_down) * 0.001
        dummy_up = torch.randn_like(final_layer_linear_up) * 0.001
        
        # 确保权重被正确添加
        state_dict[adaLN_down_key] = dummy_down
        state_dict[adaLN_up_key] = dummy_up

        if verbose:
            print(f"✅ Added adaLN weights with small random values:")
            print(f"   {adaLN_down_key} (shape: {dummy_down.shape})")
            print(f"   {adaLN_up_key} (shape: {dummy_up.shape})")
            print(f"   Note: Using small random values instead of zeros for better compatibility")
    else:
        if verbose:
            print("✅ No patch needed — adaLN weights already present or no final_layer.linear found.")

    return state_dict


def main():
    print("🔄 Universal final_layer.adaLN LoRA patcher (.safetensors)")
    
    # 设置UTF-8编码 - 使用更兼容的方式
    if sys.platform == "win32":
        try:
            import locale
            # 尝试设置UTF-8编码，如果失败则忽略
            locale.setlocale(locale.LC_ALL, 'C.UTF-8')
        except locale.Error:
            # Windows不支持C.UTF-8，使用默认编码
            pass
    
    try:
        input_path = input("Enter path to input LoRA .safetensors file: ").strip()
        output_path = input("Enter path to save patched LoRA .safetensors file: ").strip()
    except UnicodeDecodeError:
        print("⚠️  Encoding error detected. Trying to fix...")
        # 尝试使用不同的编码方式读取
        import codecs
        sys.stdin = codecs.getreader('utf-8')(sys.stdin.detach())
        input_path = input("Enter path to input LoRA .safetensors file: ").strip()
        output_path = input("Enter path to save patched LoRA .safetensors file: ").strip()

    # 验证文件路径
    if not os.path.exists(input_path):
        print(f"❌ Error: Input file not found: {input_path}")
        print(f"   Please check if the file exists and the path is correct.")
        return

    # Load
    state_dict = {}
    try:
        with safe_open(input_path, framework="pt", device="cpu") as f:
            for k in f.keys():
                state_dict[k] = f.get_tensor(k)
    except Exception as e:
        print(f"❌ Error loading file: {e}")
        return

    print(f"\n✅ Loaded {len(state_dict)} tensors from: {input_path}")

    # Show all keys that mention 'final_layer' for debug
    final_keys = [k for k in state_dict if "final_layer" in k]
    if final_keys:
        print("\n🔑 Found these final_layer-related keys:")
        for k in final_keys:
            print(f"   {k}")
    else:
        print("\n⚠️  No keys with 'final_layer' found — will try patch anyway.")

    # Try common prefixes in order
    prefixes = [
        "lora_unet_final_layer",
        "final_layer",
        "base_model.model.final_layer"
    ]
    patched = False

    for prefix in prefixes:
        before = len(state_dict)
        state_dict = patch_final_layer_adaLN(state_dict, prefix=prefix)
        after = len(state_dict)
        if after > before:
            patched = True
            break  # Stop after the first successful patch

    if not patched:
        print("\nℹ️  No patch applied — either adaLN already exists or no final_layer.linear found.")

    # Save
    try:
        safetensors.torch.save_file(state_dict, output_path)
        print(f"\n✅ Patched file saved to: {output_path}")
        print(f"   Total tensors now: {len(state_dict)}")
    except Exception as e:
        print(f"❌ Error saving file: {e}")
        return

    # Verify
    print("\n🔍 Verifying patched keys:")
    try:
        with safe_open(output_path, framework="pt", device="cpu") as f:
            keys = list(f.keys())
            for k in keys:
                if "final_layer" in k:
                    print(f"   {k}")

            has_adaLN_after = any("adaLN_modulation_1" in k for k in keys)
            print(f"✅ Contains adaLN after patch: {has_adaLN_after}")
            
            # 额外验证：检查权重值
            if has_adaLN_after:
                adaLN_keys = [k for k in keys if "adaLN_modulation_1" in k]
                print(f"🔍 Found {len(adaLN_keys)} adaLN keys:")
                for k in adaLN_keys:
                    tensor = f.get_tensor(k)
                    print(f"   {k}: shape={tensor.shape}, dtype={tensor.dtype}")
    except Exception as e:
        print(f"❌ Error verifying file: {e}")


if __name__ == "__main__":
    main()
