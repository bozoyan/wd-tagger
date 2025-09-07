<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI 智能图像反推系统</title>
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
        }
        
        .dark-mode .card {
            background: var(--dark-card);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.3), 0 8px 10px -6px rgba(99, 102, 241, 0.2);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
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
        }
        
        .dark-mode .tag-chip {
            background: rgba(147, 197, 253, 0.1);
            border: 1px solid rgba(147, 197, 253, 0.2);
            color: #93c5fd;
        }
        
        .result-section {
            border-left: 4px solid var(--primary-color);
        }
        
        .dark-mode .result-section {
            border-left: 4px solid #93c5fd;
        }
        
        .copy-btn {
            opacity: 0;
            transition: all 0.2s ease;
        }
        
        .result-item:hover .copy-btn {
            opacity: 1;
        }
        
        .glow-effect {
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.15);
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
        }
        
        @keyframes progressAnimation {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
    </style>
</head>
<body class="min-h-screen transition-colors duration-300">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <header class="mb-12 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 gradient-text animate-fade-in">
                <i class="fas fa-brain mr-3"></i>AI 智能图像反推系统
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto animate-fade-in" style="animation-delay: 0.2s;">
                基于智谱AI GLM-4.5V 模型的高级图像分析与标签生成系统
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
                <div class="card rounded-2xl p-6 shadow-xl glow-effect">
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
                    
                    <!-- Batch Processing -->
                    <div class="mb-6">
                        <h3 class="font-semibold mb-3 flex items-center">
                            <i class="fas fa-images mr-2 text-indigo-600 dark:text-indigo-400"></i>
                            批量处理
                        </h3>
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 text-center hover:border-indigo-500 dark:hover:border-indigo-400 transition-all cursor-pointer" id="batchDropArea">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 dark:text-gray-500 mb-2"></i>
                            <p class="text-sm text-gray-600 dark:text-gray-400">拖放图片文件夹或点击选择</p>
                            <input type="file" id="batchInput" class="hidden" webkitdirectory directory multiple>
                        </div>
                        <div id="batchFileList" class="mt-3 max-h-32 overflow-y-auto text-sm"></div>
                    </div>
                </div>
            </div>
            
            <!-- Image Input & Results -->
            <div class="lg:col-span-2">
                <!-- Single Image Processing -->
                <div class="card rounded-2xl p-6 shadow-xl mb-8 glow-effect">
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
                    </div>
                </div>
                
                <!-- Batch Results -->
                <div id="batchResultsContainer" class="hidden card rounded-2xl p-6 shadow-xl glow-effect">
                    <h2 class="text-2xl font-bold mb-6 flex items-center">
                        <i class="fas fa-list mr-3 text-indigo-600 dark:text-indigo-400"></i>
                        批量处理结果
                    </h2>
                    
                    <div id="batchResultsList" class="space-y-4"></div>
                    
                    <div class="mt-6 text-center">
                        <button id="exportBatchBtn" class="bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white px-6 py-3 rounded-lg font-medium transition-all flex items-center justify-center mx-auto">
                            <i class="fas fa-file-export mr-2"></i>导出所有结果
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="mt-16 text-center text-gray-500 dark:text-gray-400">
            <p>AI 智能图像反推系统 v1.0 | 基于智谱AI GLM-4.5V 模型</p>
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
        
        // Load saved API key
        const savedApiKey = localStorage.getItem('zhipuai_api_key');
        if (savedApiKey) {
            apiKeyInput.value = savedApiKey;
        }
        
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
        
        // Batch Processing
        const batchDropArea = document.getElementById('batchDropArea');
        const batchInput = document.getElementById('batchInput');
        const batchFileList = document.getElementById('batchFileList');
        
        batchDropArea.addEventListener('click', () => {
            batchInput.click();
        });
        
        batchDropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            batchDropArea.classList.add('border-indigo-500', 'dark:border-indigo-400');
        });
        
        batchDropArea.addEventListener('dragleave', () => {
            batchDropArea.classList.remove('border-indigo-500', 'dark:border-indigo-400');
        });
        
        batchDropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            batchDropArea.classList.remove('border-indigo-500', 'dark:border-indigo-400');
            
            if (e.dataTransfer.items) {
                // Use DataTransferItemList interface to access the file(s)
                for (let i = 0; i < e.dataTransfer.items.length; i++) {
                    if (e.dataTransfer.items[i].kind === 'file') {
                        const file = e.dataTransfer.items[i].getAsFile();
                        displayBatchFile(file);
                    }
                }
            } else {
                // Use DataTransfer interface to access the file(s)
                for (let i = 0; i < e.dataTransfer.files.length; i++) {
                    displayBatchFile(e.dataTransfer.files[i]);
                }
            }
        });
        
        batchInput.addEventListener('change', (e) => {
            batchFileList.innerHTML = '';
            for (let i = 0; i < e.target.files.length; i++) {
                displayBatchFile(e.target.files[i]);
            }
        });
        
        function displayBatchFile(file) {
            if (file.type.startsWith('image/')) {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between p-2 bg-gray-100 dark:bg-gray-800/50 rounded text-sm';
                div.innerHTML = `
                    <span class="truncate">${file.name}</span>
                    <span class="text-gray-500 dark:text-gray-400">${formatFileSize(file.size)}</span>
                `;
                batchFileList.appendChild(div);
            }
        }
        
        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            else return (bytes / 1048576).toFixed(1) + ' MB';
        }
        
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
        
        // Process Button
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
            
            // Show progress
            progressContainer.classList.remove('hidden');
            resultsContainer.classList.add('hidden');
            
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
                
                // Call AI API
                const result = await callZhipuAIAPI(imageUrl, apiKey, customPrompt);
                
                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                progressText.textContent = '处理完成!';
                
                // Display results after a short delay
                setTimeout(() => {
                    progressContainer.classList.add('hidden');
                    displayResults(result, imageUrl);
                    resultsContainer.classList.remove('hidden');
                    
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
            }
        });
        
        // Display Results
        function displayResults(result, imageUrl) {
            // Display image
            resultImage.src = imageUrl;
            
            // Extract content using regex
            const chineseDescMatch = result.match(/★★中文描述★★\s*([\s\S]*?)(?=■■英文描述■■|$)/);
            const englishDescMatch = result.match(/■■英文描述■■\s*([\s\S]*?)(?=【中文Tag标签】|$)/);
            const chineseTagsMatch = result.match(/【中文Tag标签】\s*([^\]]*?)(?=\[英文Tag标签\]|$)/);
            const englishTagsMatch = result.match(/\[英文Tag标签\]\s*([^\]]*?)(?=$)/);
            
            // Display descriptions
            document.getElementById('chineseDesc').textContent = chineseDescMatch ? chineseDescMatch[1].trim() : '未找到中文描述';
            document.getElementById('englishDesc').textContent = englishDescMatch ? englishDescMatch[1].trim() : '未找到英文描述';
            
            // Display tags
            const chineseTagsContainer = document.getElementById('chineseTags');
            const englishTagsContainer = document.getElementById('englishTags');
            
            chineseTagsContainer.innerHTML = '';
            englishTagsContainer.innerHTML = '';
            
            if (chineseTagsMatch && chineseTagsMatch[1]) {
                const tags = chineseTagsMatch[1].split(',').map(tag => tag.trim()).filter(tag => tag);
                tags.forEach(tag => {
                    if (tag) {
                        const tagElement = document.createElement('span');
                        tagElement.className = 'tag-chip px-3 py-1 rounded-full text-sm font-medium';
                        tagElement.textContent = tag;
                        chineseTagsContainer.appendChild(tagElement);
                    }
                });
            }
            
            if (englishTagsMatch && englishTagsMatch[1]) {
                const tags = englishTagsMatch[1].split(',').map(tag => tag.trim()).filter(tag => tag);
                tags.forEach(tag => {
                    if (tag) {
                        const tagElement = document.createElement('span');
                        tagElement.className = 'tag-chip px-3 py-1 rounded-full text-sm font-medium';
                        tagElement.textContent = tag;
                        englishTagsContainer.appendChild(tagElement);
                    }
                });
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
            const chineseTags = Array.from(document.getElementById('chineseTags').children).map(el => el.textContent).join(', ');
            const englishTags = Array.from(document.getElementById('englishTags').children).map(el => el.textContent).join(', ');
            
            const allResults = `
★★中文描述★★
${chineseDesc}

■■英文描述■■
${englishDesc}

【中文Tag标签】
${chineseTags}

[英文Tag标签]
${englishTags}
            `.trim();
            
            navigator.clipboard.writeText(allResults).then(() => {
                showNotification('所有结果已复制到剪贴板', 'success');
            }).catch(err => {
                console.error('复制失败:', err);
                showNotification('复制失败', 'error');
            });
        }
        
        // Export batch results
        const exportBatchBtn = document.getElementById('exportBatchBtn');
        exportBatchBtn.addEventListener('click', () => {
            // In a real implementation, this would export all batch results
            showNotification('批量导出功能将在完整版中实现', 'info');
        });
        
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
        
        // Mock AI API call (replace with actual API call in production)
        async function callZhipuAIAPI(imageUrl, apiKey, customPrompt = '') {
            // Simulate API delay
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            // System prompt
            const systemPrompt = "请提供这些图片的中英文详细描述，这些描述信息将用于AI绘画的prompt，中文描述用两个五角星'★★中文描述★★'符号一起包围，英文描述用两个方块'■■英文描述■■'符号一起包围。最后再将中英文的prompt描述内容简化成tag标签。中文Tag标签用中文大写书括号'【中文Tag标签】'符号一起包围，英文Tag标签用英文小写书括号'[英文Tag标签]'符号一起包围，中英文的每个tag标签在包围符号内用英文逗号分割。最后返回的四段信息分别是：中文详细信息（用于AI绘画的Prompt）、 英文详细信息（用于AI绘画的Prompt）、中文Tag标签、英文Tag标签。不要出现 markdown 标签，也不要出现注释信息。";
            
            // Combine custom prompt with system prompt if provided
            const fullPrompt = customPrompt ? `${customPrompt}\n${systemPrompt}` : systemPrompt;
            
            // In a real implementation, this would make an actual API call to ZhipuAI
            // For demo purposes, return mock data
            return `
★★中文描述★★
这是一张充满活力的城市街景照片，展现了现代都市的繁华与活力。画面中可以看到高耸的摩天大楼、繁忙的街道和熙熙攘攘的人群。阳光明媚的天气为整个场景增添了温暖的氛围，让人感受到城市的脉搏和生命力。

■■英文描述■■
This is a vibrant urban street scene photo that showcases the hustle and bustle of modern city life. The image features towering skyscrapers, busy streets, and bustling crowds. The sunny weather adds a warm atmosphere to the entire scene, making one feel the pulse and vitality of the city.

【中文Tag标签】
城市, 街景, 摩天大楼, 人群, 阳光, 繁华, 现代, 都市, 生机, 活力

[英文Tag标签]
city, street, skyscrapers, crowd, sunshine, bustling, modern, urban, vitality, energy
            `.trim();
        }
    </script>
</body>
</html>