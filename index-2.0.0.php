<?php
// 简化版本 - 主要用于API密钥加载
$parsedResponse = [];
$image_url = '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI 智能图像批量反推系统</title>
    <link href="https://cdn.staticfile.net/font-awesome/6.7.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.staticfile.net/tailwindcss/2.2.9/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@400;500;600;700&family=Noto+Sans+SC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --accent-color: #ec4899;
            --dark-bg: #0f172a;
            --dark-card: #1e293b;
            --light-bg: #f8fafc;
            --light-card: #ffffff;
        }
        
        body {
            font-family: 'Noto Sans SC', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background: var(--light-bg);
            color: #1e293b;
            transition: all 0.3s ease;
        }
        
        body.dark-mode {
            background: var(--dark-bg);
            color: #e2e8f0;
        }
        
        .card {
            background: var(--light-card);
            transition: all 0.3s ease;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .dark-mode .card {
            background: var(--dark-card);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            transition: all 0.3s ease;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.3), 0 8px 10px -6px rgba(99, 102, 241, 0.2);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .tag-chip {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            color: var(--primary-color);
            border-radius: 9999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .dark-mode .tag-chip {
            background: rgba(147, 197, 253, 0.1);
            border: 1px solid rgba(147, 197, 253, 0.2);
            color: #93c5fd;
        }
        
        .result-section {
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .dark-mode .result-section {
            border-left: 4px solid #93c5fd;
        }
        
        .copy-btn {
            opacity: 0;
            transition: all 0.2s ease;
            background: rgba(100, 116, 139, 0.1);
            border: 1px solid rgba(100, 116, 139, 0.2);
            color: #64748b;
            border-radius: 0.5rem;
            padding: 0.5rem;
            cursor: pointer;
        }
        
        .result-item:hover .copy-btn {
            opacity: 1;
        }
        
        .glow-effect {
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.15);
            transition: all 0.3s ease;
        }
        
        .dark-mode .glow-effect {
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.25);
        }
        
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .progress-bar {
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            animation: progressAnimation 2s infinite;
            border-radius: 9999px;
        }
        
        @keyframes progressAnimation {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            background: rgba(100, 116, 139, 0.05);
            transition: all 0.2s ease;
        }
        
        .dark-mode .file-item {
            background: rgba(100, 116, 139, 0.1);
        }
        
        .file-item:hover {
            background: rgba(99, 102, 241, 0.1);
            transform: translateX(5px);
        }
        
        .dark-mode .file-item:hover {
            background: rgba(147, 197, 253, 0.1);
        }
        
        .delete-btn {
            color: #ef4444;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
        }
        
        .delete-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            transform: scale(1.1);
        }
        
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background: rgba(100, 116, 139, 0.05);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .dark-mode .checkbox-item {
            background: rgba(100, 116, 139, 0.1);
        }
        
        .checkbox-item:hover {
            background: rgba(99, 102, 241, 0.1);
        }
        
        .dark-mode .checkbox-item:hover {
            background: rgba(147, 197, 253, 0.1);
        }
        
        .checkbox-item input {
            margin-right: 0.5rem;
            accent-color: var(--primary-color);
        }
        
        .folder-input {
            position: relative;
        }
        
        .folder-input input {
            padding-right: 3rem;
        }
        
        .folder-input .folder-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        /* 新增：上传区域样式 */
        #uploadForm {
            display: none;
        }

        .drag-active {
            border-color: #6366f1 !important;
            background-color: rgba(99, 102, 241, 0.05) !important;
        }
    </style>
</head>
<body class="min-h-screen transition-colors duration-300">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <header class="mb-12 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 gradient-text animate-fade-in">
                <i class="fas fa-brain mr-3"></i>AI 智能图像批量反推系统
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto animate-fade-in" style="animation-delay: 0.2s;">
                基于智谱AI GLM-4.5V 模型的高级批量图像分析与标签生成系统
            </p>
            
            <!-- Theme Toggle -->
            <div class="flex justify-center mt-8">
                <button id="themeToggle" class="p-3 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-300">
                    <i class="fas fa-moon text-lg"></i>
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Settings Panel -->
            <div class="lg:col-span-1">
                <div class="card p-6 shadow-xl glow-effect">
                    <h2 class="text-2xl font-bold mb-6 flex items-center">
                        <i class="fas fa-cog mr-3 text-indigo-600 dark:text-indigo-400"></i>
                        系统设置
                    </h2>
                    
                    <!-- API Key Input -->
                    <div class="mb-6">
                        <label for="apiKey" class="block text-sm font-medium mb-2">智谱AI API Key</label>
                        <div class="relative">
                            <input type="password" id="apiKey" placeholder="请输入您的 ZHIPUAI_API_KEY" class="w-full p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <button id="saveApiKey" class="absolute right-2 top-2 p-2 text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400">
                                <i class="fas fa-save"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">您的API密钥将安全存储在浏览器缓存中</p>
                    </div>
                    
                    <!-- Custom Prompt -->
                    <div class="mb-6">
                        <label for="customPrompt" class="block text-sm font-medium mb-2">自定义提示词</label>
                        <textarea id="customPrompt" rows="4" placeholder="例如：请重点关注图片中的服装细节..." class="w-full p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">自定义提示词将添加到系统提示前，用于指导AI分析</p>
                    </div>
                    
                    <!-- Processing Options -->
                    <div class="mb-6">
                        <h3 class="font-semibold mb-3 flex items-center">
                            <i class="fas fa-sliders-h mr-2 text-indigo-600 dark:text-indigo-400"></i>
                            处理选项
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="autoCopy" class="mr-3 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="autoCopy" class="text-sm">处理完成后自动复制结果</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="showProgress" class="mr-3 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" checked>
                                <label for="showProgress" class="text-sm">显示处理进度</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Batch Processing Settings -->
                <div class="card p-6 shadow-xl glow-effect mt-8">
                    <h2 class="text-2xl font-bold mb-6 flex items-center">
                        <i class="fas fa-folder mr-3 text-indigo-600 dark:text-indigo-400"></i>
                        批量处理设置
                    </h2>
                    
                    <!-- Folder Name Input -->
                    <div class="mb-6 folder-input">
                        <label for="folderName" class="block text-sm font-medium mb-2">文件夹名称</label>
                        <div class="relative">
                            <input type="text" id="folderName" placeholder="输入文件夹名称（留空则使用日期时间）" class="w-full p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <i class="fas fa-folder folder-icon"></i>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">指定文件夹名称，系统将自动创建并处理其中的图片</p>
                    </div>
                    
                    <!-- Tag Types Selection -->
                    <div class="mb-6">
                        <h3 class="font-semibold mb-3 flex items-center">
                            <i class="fas fa-tags mr-2 text-indigo-600 dark:text-indigo-400"></i>
                            选择打标类型
                        </h3>
                        <div class="checkbox-group">
                            <label class="checkbox-item">
                                <input type="checkbox" name="tagType" value="chineseDesc" checked>
                                <span>中文描述</span>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="tagType" value="englishDesc" checked>
                                <span>英文描述</span>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" name="tagType" value="englishTags" checked>
                                <span>英文标签</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">选择需要保存到TXT文件中的打标类型</p>
                    </div>
                    
                    <!-- Process Button -->
                    <button id="batchProcessBtn" class="btn-primary w-full py-3 font-medium flex items-center justify-center mt-4">
                        <i class="fas fa-play mr-2"></i>开始批量打标处理
                    </button>
                </div>
            </div>
            
            <!-- Image Input & Results -->
            <div class="lg:col-span-2">
                <!-- Single Image Processing -->
                <div class="card p-6 shadow-xl mb-8 glow-effect">
                    <h2 class="text-2xl font-bold mb-6 flex items-center">
                        <i class="fas fa-image mr-3 text-indigo-600 dark:text-indigo-400"></i>
                        单张图片处理
                    </h2>
                    
                    <!-- Image Input -->
                    <div class="mb-6">
                        <label for="imageInput" class="block text-sm font-medium mb-2">图片URL或上传图片</label>
                        <div class="flex gap-3">
                            <input type="text" id="imageInput" placeholder="请输入图片URL或点击上传按钮" class="flex-1 p-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <label class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-lg cursor-pointer transition-all flex items-center justify-center">
                                <i class="fas fa-upload mr-2"></i>上传
                                <input type="file" id="fileInput" class="hidden" accept="image/*">
                            </label>
                            <button id="processBtn" class="btn-primary px-6 py-3 rounded-lg font-medium flex items-center justify-center">
                                <i class="fas fa-robot mr-2"></i>开始分析
                            </button>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div id="progressContainer" class="hidden mb-6">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div id="progressBar" class="progress-bar rounded-full h-2"></div>
                        </div>
                        <p id="progressText" class="text-sm text-center mt-2 text-gray-600 dark:text-gray-400">正在处理...</p>
                    </div>
                    
                    <!-- Results -->
                    <div id="resultsContainer" class="hidden animate-fade-in">
                        <!-- Image Display -->
                        <div class="mb-6 text-center">
                            <img id="resultImage" class="max-w-full h-auto max-h-96 mx-auto rounded-lg shadow-md">
                        </div>
                        
                        <!-- Results Tabs -->
                        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                            <nav class="flex space-x-8">
                                <button class="result-tab active pb-4 font-medium text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400">
                                    <i class="fas fa-language mr-2"></i>中文描述
                                </button>
                                <button class="result-tab pb-4 font-medium text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                    <i class="fas fa-globe mr-2"></i>英文描述
                                </button>
                                <button class="result-tab pb-4 font-medium text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                    <i class="fas fa-tags mr-2"></i>中文标签
                                </button>
                                <button class="result-tab pb-4 font-medium text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                    <i class="fas fa-tag mr-2"></i>英文标签
                                </button>
                            </nav>
                        </div>
                        
                        <!-- Tab Content -->
                        <div id="tabContent" class="space-y-6">
                            <!-- Chinese Description -->
                            <div class="result-section p-6 rounded-lg bg-gray-50 dark:bg-gray-800/50 border-l-4 border-indigo-500 dark:border-indigo-400 result-item">
                                <div class="flex justify-between items-center mb-3">
                                    <h3 class="font-semibold text-lg">★★中文描述★★</h3>
                                    <button class="copy-btn p-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-gray-600 dark:text-gray-300" data-clipboard-target="#chineseDesc">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <div id="chineseDesc" class="whitespace-pre-wrap text-gray-700 dark:text-gray-300 leading-relaxed"></div>
                            </div>
                            
                            <!-- English Description -->
                            <div class="result-section p-6 rounded-lg bg-gray-50 dark:bg-gray-800/50 border-l-4 border-blue-500 dark:border-blue-400 hidden result-item">
                                <div class="flex justify-between items-center mb-3">
                                    <h3 class="font-semibold text-lg">■■英文描述■■</h3>
                                    <button class="copy-btn p-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-gray-600 dark:text-gray-300" data-clipboard-target="#englishDesc">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <div id="englishDesc" class="whitespace-pre-wrap text-gray-700 dark:text-gray-300 leading-relaxed"></div>
                            </div>
                            
                            <!-- Chinese Tags -->
                            <div class="result-section p-6 rounded-lg bg-gray-50 dark:bg-gray-800/50 border-l-4 border-green-500 dark:border-green-400 hidden result-item">
                                <div class="flex justify-between items-center mb-3">
                                    <h3 class="font-semibold text-lg">【中文Tag标签】</h3>
                                    <button class="copy-btn p-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-green-100 dark:hover:bg-green-900/50 text-gray-600 dark:text-gray-300" data-clipboard-target="#chineseTags">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <div id="chineseTags" class="flex flex-wrap gap-2 mt-3"></div>
                            </div>
                            
                            <!-- English Tags -->
                            <div class="result-section p-6 rounded-lg bg-gray-50 dark:bg-gray-800/50 border-l-4 border-purple-500 dark:border-purple-400 hidden result-item">
                                <div class="flex justify-between items-center mb-3">
                                    <h3 class="font-semibold text-lg">[英文Tag标签]</h3>
                                    <button class="copy-btn p-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-purple-100 dark:hover:bg-purple-900/50 text-gray-600 dark:text-gray-300" data-clipboard-target="#englishTags">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <div id="englishTags" class="flex flex-wrap gap-2 mt-3"></div>
                            </div>
                        </div>

                       <!-- Copy All Button -->
                       <div class="mt-6 text-center">
                            <button id="copyAllBtn" class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-6 py-3 rounded-lg font-medium transition-all flex items-center justify-center mx-auto">
                                <i class="fas fa-copy mr-2"></i>复制全部结果
                            </button>
                        </div>  

                    <!-- Response Info Display -->
                    <div id="responseInfo" class="mb-6 hidden">
                        <h3 class="font-semibold mb-3 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-indigo-600 dark:text-indigo-400"></i>
                            响应信息
                        </h3>
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
                            <pre id="responseContent" class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap"></pre>
                        </div>
                    </div>

                       
                    </div>
                </div>
                
                <!-- Batch Processing -->
                <div class="card p-6 shadow-xl glow-effect">
                    <h2 class="text-2xl font-bold mb-6 flex items-center">
                        <i class="fas fa-images mr-3 text-indigo-600 dark:text-indigo-400"></i>
                        批量打标处理
                    </h2>
                    
                    
                    <!-- Hidden Form for File Upload -->
                    <form id="uploadForm" enctype="multipart/form-data">
                        <input type="hidden" name="folderName" id="formFolderName">
                        <input type="hidden" name="customPrompt" id="formCustomPrompt">
                        <input type="hidden" name="apiKey" id="formApiKey">
                        <input type="hidden" name="tagTypes" id="formTagTypes">
                        <input type="file" name="images[]" id="batchInput" class="hidden" webkitdirectory directory multiple accept="image/*">
                    </form>
                    
                    <!-- Drag & Drop Area -->
                    <div class="mb-6 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-indigo-500 dark:hover:border-indigo-400 transition-all cursor-pointer" id="batchDropArea">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 dark:text-gray-500 mb-4"></i>
                        <h3 class="text-lg font-medium mb-2">拖放图片文件夹或点击选择</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">支持 JPG, PNG, GIF, WEBP 格式</p>
                    </div>
                    
                    <!-- File List -->
                    <div id="batchFileList" class="mt-6">
                        <h3 class="font-semibold mb-4 flex items-center">
                            <i class="fas fa-list mr-2 text-indigo-600 dark:text-indigo-400"></i>
                            已选择文件 (<span id="fileCount">0</span>)
                        </h3>
                        <div id="fileListContainer" class="space-y-2 max-h-64 overflow-y-auto"></div>
                        <div class="mt-4 flex justify-between items-center">
                            <button id="clearFileList" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-all">
                                <i class="fas fa-trash mr-2"></i>清空列表
                            </button>
                            <span class="text-sm text-gray-500 dark:text-gray-400">总大小: <span id="totalSize">0 KB</span></span>
                        </div>
                    </div>
                    
                    <!-- Batch Progress -->
                    <div id="batchProgressContainer" class="hidden mt-6">
                        <h3 class="font-semibold mb-3">批量处理进度</h3>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div id="batchProgressBar" class="progress-bar rounded-full h-3"></div>
                        </div>
                        <p id="batchProgressText" class="text-sm text-center mt-2 text-gray-600 dark:text-gray-400">正在处理第 <span id="currentFileNum">0</span> 张图片，共 <span id="totalFileNum">0</span> 张</p>
                    </div>
                    
                    <!-- Batch Results -->
                    <div id="batchResultsContainer" class="hidden mt-8">
                        <h3 class="font-semibold mb-4 flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            处理完成
                        </h3>
                        <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                            <p class="text-green-700 dark:text-green-300 mb-2">
                                <i class="fas fa-check mr-2"></i>成功处理 <span id="successCount">0</span> 张图片
                            </p>
                            <p class="text-green-700 dark:text-green-300 mb-2">
                                <i class="fas fa-times mr-2"></i>失败 <span id="failCount">0</span> 张图片
                            </p>
                            <p class="text-green-700 dark:text-green-300">
                                <i class="fas fa-folder mr-2"></i>结果保存在: <span id="resultFolder">output/</span>
                            </p>
                        </div>
                        <div class="mt-4 flex justify-center">
                            <a id="downloadLink" href="#" class="bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white px-6 py-3 rounded-lg font-medium transition-all flex items-center justify-center">
                                <i class="fas fa-download mr-2"></i>下载所有结果
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-16 text-center text-gray-500 dark:text-gray-400">
            <p>AI 智能图像批量反推系统 v2.0 | 基于智谱AI GLM-4.5V 模型</p>
            <p class="mt-2 text-sm">© 2024 AI 图像分析系统. 保留所有权利.</p>
        </footer>
    </div>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        
        // Check for saved theme preference or use system preference
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            body.classList.add('dark-mode');
            themeToggle.innerHTML = '<i class="fas fa-sun text-lg"></i>';
        }
        
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                themeToggle.innerHTML = '<i class="fas fa-sun text-lg"></i>';
                localStorage.setItem('theme', 'dark');
            } else {
                themeToggle.innerHTML = '<i class="fas fa-moon text-lg"></i>';
                localStorage.setItem('theme', 'light');
            }
        });
        
        // API Key Management
        const apiKeyInput = document.getElementById('apiKey');
        const saveApiKeyBtn = document.getElementById('saveApiKey');
        
        // Function to load API key from BOZO.env file
        async function loadApiKeyFromEnv() {
            try {
                const response = await fetch('BOZO.env');
                if (response.ok) {
                    const content = await response.text();
                    const match = content.match(/^ZHIPUAI_API_KEY\s*=\s*(.+)$/m);
                    if (match && match[1]) {
                        return match[1].trim();
                    }
                }
            } catch (error) {
                console.log('BOZO.env file not found or not accessible');
            }
            return null;
        }
        
        // Load API key: first from BOZO.env, then from localStorage
        async function initializeApiKey() {
            const envApiKey = await loadApiKeyFromEnv();
            if (envApiKey) {
                apiKeyInput.value = envApiKey;
                // Also save to localStorage for consistency
                localStorage.setItem('zhipuai_api_key', envApiKey);
            } else {
                const savedApiKey = localStorage.getItem('zhipuai_api_key');
                if (savedApiKey) {
                    apiKeyInput.value = savedApiKey;
                }
            }
        }
        
        // Initialize API key on page load
        initializeApiKey();
        
        // 页面加载时的初始化
        document.addEventListener('DOMContentLoaded', function() {
            console.log('页面加载完成，等待用户操作');
        });
        
        saveApiKeyBtn.addEventListener('click', () => {
            const apiKey = apiKeyInput.value.trim();
            if (apiKey) {
                localStorage.setItem('zhipuai_api_key', apiKey);
                showNotification('API密钥已保存', 'success');
            } else {
                showNotification('请输入API密钥', 'error');
            }
        });
        
            
        // Image Input Handling
        const imageInput = document.getElementById('imageInput');
        const fileInput = document.getElementById('fileInput');
        
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const reader = new FileReader();
                
                reader.onload = (event) => {
                    imageInput.value = event.target.result;
                };
                
                reader.readAsDataURL(file);
            }
        });
        
        // Batch Processing - Drag & Drop
        const batchDropArea = document.getElementById('batchDropArea');
        const batchInput = document.getElementById('batchInput');
        const batchFileList = document.getElementById('batchFileList');
        const fileListContainer = document.getElementById('fileListContainer');
        const fileCount = document.getElementById('fileCount');
        const totalSize = document.getElementById('totalSize');
        const clearFileListBtn = document.getElementById('clearFileList');
        const uploadForm = document.getElementById('uploadForm');
        
        let selectedFiles = [];
        
        batchDropArea.addEventListener('click', () => {
            batchInput.click();
        });
        
        batchInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
        
        batchDropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            batchDropArea.classList.add('drag-active');
        });
        
        batchDropArea.addEventListener('dragleave', () => {
            batchDropArea.classList.remove('drag-active');
        });
        
        batchDropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            batchDropArea.classList.remove('drag-active');
            handleFiles(e.dataTransfer.files);
        });
        
        function handleFiles(files) {
            selectedFiles = [];
            fileListContainer.innerHTML = '';
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    selectedFiles.push(file);
                }
            }
            
            displayBatchFiles();
        }
        
        function displayBatchFiles() {
            fileListContainer.innerHTML = '';
            let totalFileSize = 0;
            
            selectedFiles.forEach((file, index) => {
                totalFileSize += file.size;
                
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-image text-indigo-500 mr-3"></i>
                        <span class="truncate">${file.name}</span>
                        <span class="ml-3 text-xs text-gray-500 dark:text-gray-400">${formatFileSize(file.size)}</span>
                    </div>
                    <button class="delete-btn" data-index="${index}">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                fileListContainer.appendChild(fileItem);
            });
            
            fileCount.textContent = selectedFiles.length;
            totalSize.textContent = formatFileSize(totalFileSize);
            
            // Add delete event listeners
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const index = parseInt(e.target.closest('.delete-btn').getAttribute('data-index'));
                    selectedFiles.splice(index, 1);
                    displayBatchFiles();
                });
            });
        }
        
        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            else return (bytes / 1048576).toFixed(1) + ' MB';
        }
        
        // Clear file list
        clearFileListBtn.addEventListener('click', () => {
            selectedFiles = [];
            fileListContainer.innerHTML = '';
            fileCount.textContent = '0';
            totalSize.textContent = '0 KB';
        });
        
        // Tab Navigation
        const resultTabs = document.querySelectorAll('.result-tab');
        const tabContents = document.querySelectorAll('#tabContent > div');
        
        resultTabs.forEach((tab, index) => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                resultTabs.forEach(t => t.classList.remove('active', 'text-indigo-600', 'dark:text-indigo-400', 'border-indigo-600', 'dark:border-indigo-400'));
                tab.classList.add('active', 'text-indigo-600', 'dark:text-indigo-400', 'border-indigo-600', 'dark:border-indigo-400');
                
                // Hide all tab contents
                tabContents.forEach(content => content.classList.add('hidden'));
                // Show selected tab content
                tabContents[index].classList.remove('hidden');
            });
        });
        
        // Process Button - Single Image
        const processBtn = document.getElementById('processBtn');
        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const resultsContainer = document.getElementById('resultsContainer');
        const resultImage = document.getElementById('resultImage');
        
        processBtn.addEventListener('click', async () => {
            const imageUrl = imageInput.value.trim();
            const apiKey = apiKeyInput.value.trim() || localStorage.getItem('zhipuai_api_key');
            const customPrompt = document.getElementById('customPrompt').value.trim();
            
            if (!imageUrl) {
                showNotification('请输入图片URL或上传图片', 'error');
                return;
            }
            
            if (!apiKey) {
                showNotification('请先设置智谱AI API密钥', 'error');
                return;
            }
            
            // Clear previous data
            clearPreviousResults();
            
            // Show progress
            progressContainer.classList.remove('hidden');
            resultsContainer.classList.add('hidden');
            
            // Hide response info
            document.getElementById('responseInfo').classList.add('hidden');
            
            try {
                // Simulate progress
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += 5;
                    if (progress > 100) {
                        progress = 100;
                        clearInterval(progressInterval);
                    }
                    progressBar.style.width = progress + '%';
                    progressText.textContent = `正在处理... ${progress}%`;
                }, 200);
                
                // Prepare image data for API
                let imageData;
                if (imageUrl.startsWith('data:')) {
                    // File upload (base64)
                    imageData = imageUrl;
                } else {
                    // URL input
                    imageData = imageUrl;
                }
                
                // Call AI API
                const result = await callZhipuAIAPI(imageData, apiKey, customPrompt);
                
                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                progressText.textContent = '处理完成!';
                
                // 直接显示结果，不重新加载页面
                setTimeout(() => {
                    progressContainer.classList.add('hidden');
                    displayResults(result, imageUrl);
                    resultsContainer.classList.remove('hidden');
                    
                    // 显示响应信息
                    const responseInfo = document.getElementById('responseInfo');
                    const responseContent = document.getElementById('responseContent');
                    responseContent.textContent = result;
                    responseInfo.classList.remove('hidden');
                    
                    // Auto copy if enabled
                    if (document.getElementById('autoCopy').checked) {
                        copyAllResults();
                        showNotification('结果已自动复制到剪贴板', 'success');
                    }
                }, 500);
                
            } catch (error) {
                progressContainer.classList.add('hidden');
                showNotification('处理失败: ' + error.message, 'error');
                console.error('Error:', error);
                
                // Display error response info
                const responseInfo = document.getElementById('responseInfo');
                const responseContent = document.getElementById('responseContent');
                responseContent.textContent = 'Error: ' + error.message;
                responseInfo.classList.remove('hidden');
            }
        });
        
        // Batch Process Button
        const batchProcessBtn = document.getElementById('batchProcessBtn');
        const batchProgressContainer = document.getElementById('batchProgressContainer');
        const batchProgressBar = document.getElementById('batchProgressBar');
        const batchProgressText = document.getElementById('batchProgressText');
        const currentFileNum = document.getElementById('currentFileNum');
        const totalFileNum = document.getElementById('totalFileNum');
        const batchResultsContainer = document.getElementById('batchResultsContainer');
        const successCount = document.getElementById('successCount');
        const failCount = document.getElementById('failCount');
        const resultFolder = document.getElementById('resultFolder');
        const downloadLink = document.getElementById('downloadLink');
        const formFolderName = document.getElementById('formFolderName');
        const formCustomPrompt = document.getElementById('formCustomPrompt');
        const formApiKey = document.getElementById('formApiKey');
        const formTagTypes = document.getElementById('formTagTypes');
        
        batchProcessBtn.addEventListener('click', async () => {
            if (selectedFiles.length === 0) {
                showNotification('请先选择要处理的图片', 'error');
                return;
            }
            
            const apiKey = apiKeyInput.value.trim() || localStorage.getItem('zhipuai_api_key');
            const folderNameInput = document.getElementById('folderName');
            const folderName = folderNameInput.value.trim();
            const customPrompt = document.getElementById('customPrompt').value.trim();
            
            if (!apiKey) {
                showNotification('请先设置智谱AI API密钥', 'error');
                return;
            }
            
            // Get selected tag types
            const selectedTagTypes = [];
            document.querySelectorAll('input[name="tagType"]:checked').forEach(checkbox => {
                selectedTagTypes.push(checkbox.value);
            });
            
            if (selectedTagTypes.length === 0) {
                showNotification('请至少选择一种打标类型', 'error');
                return;
            }
            
            // Update form fields
            formFolderName.value = folderName || '';
            formCustomPrompt.value = customPrompt;
            formApiKey.value = apiKey;
            formTagTypes.value = JSON.stringify(selectedTagTypes);
            
            // Create FormData object
            const formData = new FormData(uploadForm);
            selectedFiles.forEach(file => {
                formData.append('images[]', file);
            });
            
            // Show progress container
            batchProgressContainer.classList.remove('hidden');
            batchResultsContainer.classList.add('hidden');
            
            // Hide response info
            document.getElementById('responseInfo').classList.add('hidden');
            
            try {
                const response = await fetch('process.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('服务器处理失败');
                }
                
                const result = await response.json();
                
                // Display response info
                const responseInfo = document.getElementById('responseInfo');
                const responseContent = document.getElementById('responseContent');
                responseContent.textContent = JSON.stringify(result, null, 2);
                responseInfo.classList.remove('hidden');
                
                if (result.error) {
                    throw new Error(result.error);
                }
                
                // Update UI with results
                successCount.textContent = result.success;
                failCount.textContent = result.fail;
                resultFolder.textContent = result.outputDir;
                downloadLink.href = result.zipFile;
                
                batchProgressContainer.classList.add('hidden');
                batchResultsContainer.classList.remove('hidden');
                
                showNotification('批量处理完成！', 'success');
                
            } catch (error) {
                showNotification('处理失败: ' + error.message, 'error');
                batchProgressContainer.classList.add('hidden');
                
                // Display error response info
                const responseInfo = document.getElementById('responseInfo');
                const responseContent = document.getElementById('responseContent');
                responseContent.textContent = 'Error: ' + error.message;
                responseInfo.classList.remove('hidden');
            }
        });
        
        // Display Results - 按照文档要求处理数据
        function displayResults(result, imageUrl) {
            // Display image
            resultImage.src = imageUrl;
            
            // Clear previous results
            document.getElementById('chineseDesc').textContent = '';
            document.getElementById('englishDesc').textContent = '';
            document.getElementById('chineseTags').innerHTML = '';
            document.getElementById('englishTags').innerHTML = '';
            
            console.log('开始解析响应数据:', result);
            console.log('响应数据前500个字符:', result.substring(0, 500));
            console.log('响应数据是否包含thinking标签:', result.includes('<|begin_of_box|>'));
            console.log('响应数据是否包含end标签:', result.includes('<|end_of_box|>'));
            
            // 处理包含在<|begin_of_box|>和<|end_of_box|>标签中的JSON数据
            let jsonString = null;
            
            // 查找<|begin_of_box|>和<|end_of_box|>标签的位置
            const beginIndex = result.indexOf('<|begin_of_box|>');
            const endIndex = result.indexOf('<|end_of_box|>');
            
            console.log('begin标签位置:', beginIndex);
            console.log('end标签位置:', endIndex);
            
            // 如果找到了两个标签，则提取它们之间的内容
            if (beginIndex !== -1 && endIndex !== -1 && endIndex > beginIndex) {
                jsonString = result.substring(beginIndex + '<|begin_of_box|>'.length, endIndex);
                console.log('提取的JSON字符串:', jsonString);
            } else {
                // 如果没有找到标签，则尝试处理整个内容
                jsonString = result;
            }
            
            // 尝试解析JSON数据
            try {
                const jsonData = JSON.parse(jsonString);
                console.log('JSON解析成功:', jsonData);
                
                // 按照文档要求提取数据：
                // 1. 中文描述：显示 cn 字段的内容
                const chineseDesc = jsonData.cn || '';
                // 2. 英文描述：显示 en 字段的内容  
                const englishDesc = jsonData.en || '';
                // 3. 中文标签：将 cn-tag 字段按中文逗号（，）分割并显示为标签
                const chineseTagsRaw = jsonData['cn-tag'] || '';
                // 4. 英文标签：将 en-tag 字段按英文逗号（,）分割并显示为标签
                const englishTagsRaw = jsonData['en-tag'] || '';
                
                // 显示描述
                document.getElementById('chineseDesc').textContent = chineseDesc || '未找到中文描述';
                document.getElementById('englishDesc').textContent = englishDesc || '未找到英文描述';
                
                // 处理中文标签
                const chineseTagsContainer = document.getElementById('chineseTags');
                if (chineseTagsRaw) {
                    const tags = chineseTagsRaw.split('，').map(tag => tag.trim()).filter(tag => tag);
                    console.log('中文标签分割结果:', tags);
                    tags.forEach(tag => {
                        if (tag) {
                            const tagElement = document.createElement('span');
                            tagElement.className = 'tag-chip';
                            tagElement.textContent = tag;
                            chineseTagsContainer.appendChild(tagElement);
                        }
                    });
                }
                
                // 处理英文标签
                const englishTagsContainer = document.getElementById('englishTags');
                if (englishTagsRaw) {
                    const tags = englishTagsRaw.split(',').map(tag => tag.trim()).filter(tag => tag);
                    console.log('英文标签分割结果:', tags);
                    tags.forEach(tag => {
                        if (tag) {
                            const tagElement = document.createElement('span');
                            tagElement.className = 'tag-chip';
                            tagElement.textContent = tag;
                            englishTagsContainer.appendChild(tagElement);
                        }
                    });
                }
                
                console.log('数据显示完成:', {
                    cn: chineseDesc,
                    en: englishDesc,
                    'cn-tag': chineseTagsRaw,
                    'en-tag': englishTagsRaw
                });
                
            } catch (e) {
                console.error('JSON解析失败:', e);
                console.log('失败时的JSON字符串:', jsonString);
                document.getElementById('chineseDesc').textContent = '解析响应数据失败: ' + e.message;
            }
        }
        
        // Copy functionality
        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-clipboard-target');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    let textToCopy;
                    if (targetElement.tagName === 'DIV' && targetElement.children.length > 0) {
                        // For tag containers
                        const tags = Array.from(targetElement.children).map(el => el.textContent);
                        textToCopy = tags.join(', ');
                    } else {
                        // For text content
                        textToCopy = targetElement.textContent;
                    }
                    
                    navigator.clipboard.writeText(textToCopy).then(() => {
                        showNotification('已复制到剪贴板', 'success');
                    }).catch(err => {
                        console.error('复制失败:', err);
                        showNotification('复制失败', 'error');
                    });
                }
            });
        });
        
        // Copy all results
        const copyAllBtn = document.getElementById('copyAllBtn');
        copyAllBtn.addEventListener('click', copyAllResults);
        
        function copyAllResults() {
            const chineseDesc = document.getElementById('chineseDesc').textContent;
            const englishDesc = document.getElementById('englishDesc').textContent;
            const chineseTags = Array.from(document.getElementById('chineseTags').children).map(el => el.textContent).join('，');
            const englishTags = Array.from(document.getElementById('englishTags').children).map(el => el.textContent).join(', ');
            
            // Format as JSON string
            const allResults = JSON.stringify({
                "cn": chineseDesc,
                "en": englishDesc,
                "cn-tag": chineseTags,
                "en-tag": englishTags
            }, null, 2);
            
            navigator.clipboard.writeText(allResults).then(() => {
                showNotification('所有结果已复制到剪贴板', 'success');
            }).catch(err => {
                console.error('复制失败:', err);
                showNotification('复制失败', 'error');
            });
        }
        
        // Notification system
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full`;
            
            let bgColor, textColor, icon;
            switch (type) {
                case 'success':
                    bgColor = 'bg-green-500';
                    textColor = 'text-white';
                    icon = 'fas fa-check-circle';
                    break;
                case 'error':
                    bgColor = 'bg-red-500';
                    textColor = 'text-white';
                    icon = 'fas fa-exclamation-circle';
                    break;
                case 'info':
                    bgColor = 'bg-blue-500';
                    textColor = 'text-white';
                    icon = 'fas fa-info-circle';
                    break;
                default:
                    bgColor = 'bg-gray-500';
                    textColor = 'text-white';
                    icon = 'fas fa-bell';
            }
            
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="${icon} mr-3"></i>
                    <span>${message}</span>
                </div>
            `;
            notification.classList.add(bgColor, textColor);
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
                notification.classList.add('translate-x-0');
            }, 100);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.classList.remove('translate-x-0');
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
        
        // Clear previous results
        function clearPreviousResults() {
            // Clear text content
            document.getElementById('chineseDesc').textContent = '';
            document.getElementById('englishDesc').textContent = '';
            
            // Clear tag containers
            document.getElementById('chineseTags').innerHTML = '';
            document.getElementById('englishTags').innerHTML = '';
            
            // Reset to first tab
            const resultTabs = document.querySelectorAll('.result-tab');
            const tabContents = document.querySelectorAll('#tabContent > div');
            
            resultTabs.forEach(t => t.classList.remove('active', 'text-indigo-600', 'dark:text-indigo-400', 'border-indigo-600', 'dark:border-indigo-400'));
            tabContents.forEach(content => content.classList.add('hidden'));
            
            if (resultTabs.length > 0) {
                resultTabs[0].classList.add('active', 'text-indigo-600', 'dark:text-indigo-400', 'border-indigo-600', 'dark:border-indigo-400');
                tabContents[0].classList.remove('hidden');
            }
        }
        
        // Call Zhipu AI API
        async function callZhipuAIAPI(imageData, apiKey, customPrompt = '') {
            // 系统提示词
            const systemPrompt = "你是一个AI绘画提示词专家。请根据我提供的图片进行文字描述，形成用于AI绘画的一段非常丰富的中英文画面详细描述，这些描述信息将重新用于AI绘画的prompt，并且另将中英文的prompt描述内容分别简化成对应的tag标签。最后返回的是一个 json 的数组数据信息， json 里面包含四段信息分别是：cn（用于AI绘画的中文详细自然语言信息Prompt）、en （用于AI绘画的英文详细信息Prompt）、cn-tag（中文Tag标签用中文逗号内部区分）、en-tag(英文Tag标签用英文逗号内部隔开)。不需要输出无用的````````信息，直接输出 json 格式的数据。";
            
            const fullPrompt = customPrompt ? customPrompt + "\n" + systemPrompt : systemPrompt;
            
            // 添加重试机制
            const maxRetries = 3;
            const retryDelay = 5000; // 5秒
            
            for (let attempt = 1; attempt <= maxRetries; attempt++) {
                try {
                    const response = await fetch('https://open.bigmodel.cn/api/paas/v4/chat/completions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${apiKey}`
                        },
                        body: JSON.stringify({
                            model: 'glm-4.5v',
                            messages: [
                                {
                                    role: 'user',
                                    content: [
                                        {
                                            type: 'text',
                                            text: fullPrompt
                                        },
                                        {
                                            type: 'image_url',
                                            image_url: {
                                                url: imageData
                                            }
                                        }
                                    ]
                                }
                            ],
                            thinking: {
                                type: 'disabled'
                            },
                            max_tokens: 1024
                        })
                    });
                    
                    if (response.status === 429) {
                        // 处理429错误
                        if (attempt < maxRetries) {
                            console.log(`Attempt ${attempt} failed with 429. Retrying in ${retryDelay}ms...`);
                            await new Promise(resolve => setTimeout(resolve, retryDelay));
                            continue;
                        } else {
                            throw new Error('请求过于频繁，请稍后再试 (429 Too Many Requests)');
                        }
                    }
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    const aiContent = data.choices[0].message.content;
                    
                    // 直接返回原始AI内容，让PHP来处理解析
                    return aiContent;
                } catch (error) {
                    if (attempt >= maxRetries) {
                        throw error;
                    }
                    console.log(`Attempt ${attempt} failed: ${error.message}. Retrying...`);
                    await new Promise(resolve => setTimeout(resolve, retryDelay));
                }
            }
        }
        
        // 复制全部结果功能
        function copyAllResults() {
            const chineseDesc = document.getElementById('chineseDesc').textContent;
            const englishDesc = document.getElementById('englishDesc').textContent;
            const chineseTags = Array.from(document.getElementById('chineseTags').children).map(el => el.textContent).join('，');
            const englishTags = Array.from(document.getElementById('englishTags').children).map(el => el.textContent).join(',');
            
            // 检查是否包含错误信息
            if (chineseDesc.includes('解析响应数据失败')) {
                showNotification('当前显示的是错误信息，无法复制', 'error');
                return;
            }
            
            // 格式化JSON字符串
            const allResults = {
                "cn": chineseDesc,
                "en": englishDesc,
                "cn-tag": chineseTags,
                "en-tag": englishTags
            };
            
            navigator.clipboard.writeText(JSON.stringify(allResults, null, 2)).then(() => {
                showNotification('所有结果已复制到剪贴板', 'success');
            }).catch(err => {
                console.error('复制失败:', err);
                showNotification('复制失败', 'error');
            });
        }
        
        // 为复制全部按钮添加事件监听器
        document.addEventListener('DOMContentLoaded', function() {
            const copyAllBtn = document.getElementById('copyAllBtn');
            if (copyAllBtn) {
                copyAllBtn.addEventListener('click', copyAllResults);
            }
        });
    </script>
</body>
</html>
