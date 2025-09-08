<?php
header('Content-Type: application/json; charset=utf-8');

// 允许跨域请求（根据您的部署环境调整）
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// 检查是否为POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => '仅支持POST请求']);
    exit;
}

// 获取表单数据
$folderName = $_POST['folderName'] ?? date('Ymd_His');
$customPrompt = $_POST['customPrompt'] ?? '';
$apiKey = $_POST['apiKey'] ?? '';
$tagTypesJson = $_POST['tagTypes'] ?? '[]';

// 函数：从@BOZO.env文件读取API密钥
function loadApiKeyFromEnv() {
    $envFile = 'BOZO.env';
    if (file_exists($envFile)) {
        $content = file_get_contents($envFile);
        if ($content !== false) {
            // 查找 ZHIPUAI_API_KEY=your_api_key_here 格式
            if (preg_match('/^ZHIPUAI_API_KEY\s*=\s*(.+)$/m', $content, $matches)) {
                return trim($matches[1]);
            }
        }
    }
    return null;
}

// 优先从@BOZO.env文件获取API密钥，如果没有则使用表单提交的值
if (empty($apiKey)) {
    $apiKey = loadApiKeyFromEnv();
}

// 解析标签类型
$selectedTagTypes = json_decode($tagTypesJson, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $selectedTagTypes = [];
}

// 检查API密钥
if (empty($apiKey)) {
    echo json_encode(['error' => 'API密钥不能为空']);
    exit;
}

// 创建输出目录
$outputDir = "output/" . $folderName . "/";
if (!is_dir($outputDir)) {
    if (!mkdir($outputDir, 0755, true)) {
        echo json_encode(['error' => '无法创建输出目录']);
        exit;
    }
}

// 处理上传的文件
$success = 0;
$fail = 0;
$uploadedFiles = [];

if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    $files = $_FILES['images'];
    
    // 重新组织文件数组
    $fileCount = count($files['name']);
    for ($i = 0; $i < $fileCount; $i++) {
        if ($files['error'][$i] == UPLOAD_ERR_OK) {
            $uploadedFiles[] = [
                'name' => $files['name'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'type' => $files['type'][$i],
                'size' => $files['size'][$i]
            ];
        }
    }
    
    // 按文件名排序
    usort($uploadedFiles, function($a, $b) {
        return strnatcasecmp($a['name'], $b['name']);
    });
    
    // 处理每个文件
    foreach ($uploadedFiles as $index => $file) {
        $fileNum = $index + 1;
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newImageName = $fileNum . "." . $fileExtension;
        $newImagePath = $outputDir . $newImageName;
        
        // 保存重命名后的图片
        if (!move_uploaded_file($file['tmp_name'], $newImagePath)) {
            $fail++;
            continue;
        }
        
        try {
            // 调用智谱AI API
            $aiResult = callZhipuAIAPI($newImagePath, $apiKey, $customPrompt);
            
            if (isset($aiResult['error'])) {
                throw new Exception($aiResult['error']);
            }
            
            // 根据选择的类型，构建TXT内容
            $txtContent = "";
            if (in_array('chineseDesc', $selectedTagTypes) && !empty($aiResult['chineseDesc'])) {
                $txtContent .= "★★中文描述★★\n" . $aiResult['chineseDesc'] . "\n\n";
            }
            if (in_array('englishDesc', $selectedTagTypes) && !empty($aiResult['englishDesc'])) {
                $txtContent .= "■■英文描述■■\n" . $aiResult['englishDesc'] . "\n\n";
            }
            if (in_array('englishTags', $selectedTagTypes) && !empty($aiResult['englishTags'])) {
                $txtContent .= "[英文Tag标签]\n" . $aiResult['englishTags'] . "\n";
            }
            
            // 保存TXT文件
            $txtFileName = $fileNum . ".txt";
            file_put_contents($outputDir . $txtFileName, $txtContent);
            
            $success++;
            
        } catch (Exception $e) {
            error_log("处理文件 {$file['name']} 时出错: " . $e->getMessage());
            $fail++;
        }
    }
} else {
    echo json_encode(['error' => '没有上传任何文件']);
    exit;
}

// 创建ZIP文件
$zipFileName = "output_{$folderName}.zip";
$zip = new ZipArchive();
if ($zip->open($outputDir . $zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    // 添加目录中的所有文件到ZIP
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($outputDir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($files as $file) {
        if ($file->isFile() && $file->getFilename() !== $zipFileName) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($outputDir));
            $zip->addFile($filePath, $relativePath);
        }
    }
    $zip->close();
    
    $zipFilePath = $outputDir . $zipFileName;
} else {
    $zipFilePath = "";
}

// 返回JSON响应
$response = [
    'success' => $success,
    'fail' => $fail,
    'outputDir' => $outputDir,
    'zipFile' => $zipFilePath ? $zipFilePath : ''
];

echo json_encode($response);

/**
 * 调用智谱AI API 的函数
 */
function callZhipuAIAPI($imagePath, $apiKey, $customPrompt = '') {
    // 检查文件是否存在
    if (!file_exists($imagePath)) {
        return ['error' => '图片文件不存在'];
    }
    
    // 读取图片并转换为Base64
    $imageData = file_get_contents($imagePath);
    if ($imageData === false) {
        return ['error' => '无法读取图片文件'];
    }
    $base64Image = base64_encode($imageData);
    $mimeType = mime_content_type($imagePath);
    
    // 构建系统提示
    $systemPrompt = "你是一个AI绘画提示词专家。请根据我提供的图片进行文字描述，形成用于AI绘画的一段非常丰富的中英文画面详细描述，这些描述信息将重新用于AI绘画的prompt，并且另将中英文的prompt描述内容分别简化成对应的tag标签。最后返回的是一个 json 的数组数据信息， json 里面包含四段信息分别是：cn（用于AI绘画的中文详细自然语言信息Prompt）、en （用于AI绘画的英文详细信息Prompt）、cn-tag（中文Tag标签用中文逗号内部区分）、en-tag(英文Tag标签用英文逗号内部隔开)。不需要输出无用的````信息，直接输出 json 格式的数据。";
    
    $fullPrompt = $customPrompt ? $customPrompt . "\n" . $systemPrompt : $systemPrompt;
    
    // 准备API请求
    $url = "https://open.bigmodel.cn/api/paas/v4/chat/completions";
    $data = [
        "model" => "glm-4.5v",
        "messages" => [
            [
                "role" => "user",
                "content" => [
                    [
                        "type" => "text",
                        "text" => $fullPrompt
                    ],
                    [
                        "type" => "image_url",
                        "image_url" => [
                            "url" => "data:{$mimeType};base64,{$base64Image}"
                        ]
                    ]
                ]
            ]
        ],
        "thinking" => [
            "type" => "disabled"
        ]
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => [
                "Content-Type: application/json",
                "Authorization: Bearer {$apiKey}"
            ],
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === false) {
        return ['error' => 'API请求失败'];
    }
    
    $response = json_decode($result, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'API响应格式错误'];
    }
    
    if (isset($response['error'])) {
        return ['error' => $response['error']['message']];
    }
    
    // 提取AI返回的内容
    $aiContent = $response['choices'][0]['message']['content'] ?? '';
    
    // 调试输出
    error_log("Process.php - 原始AI内容: " . substr($aiContent, 0, 200));
    
    // 处理thinking标签 - 移除<\|begin_of_thought\|>和<\|end_of_thought\|>标签之间的内容
    $cleanContent = preg_replace('/<\|begin_of_box\|>.*?<\|end_of_box\|>/s', '', $aiContent);
    $cleanContent = trim($cleanContent);
    
    // 调试输出
    error_log("Process.php - 清理后内容: " . substr($cleanContent, 0, 200));
    
    // 尝试解析JSON数据
    $jsonData = null;
    if (!empty($cleanContent)) {
        $jsonData = json_decode($cleanContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonData = null;
        }
    }
    
    // 如果上面的方法失败了，尝试直接从原始内容中提取JSON
    if (!$jsonData) {
        // 使用正则表达式提取<|begin_of_box|>和<|end_of_box|>之间的内容
        if (preg_match('/<\|begin_of_box\|>(.*?)<\|end_of_box\|>/s', $aiContent, $matches)) {
            $jsonString = trim($matches[1]);
            $jsonData = json_decode($jsonString, true);
            
            // 如果还是解析失败，尝试清理字符串
            if (json_last_error() !== JSON_ERROR_NONE) {
                // 移除可能的多余字符
                $jsonString = preg_replace('/^\s*```(?:json)?\s*/', '', $jsonString);
                $jsonString = preg_replace('/\s*```\s*$/', '', $jsonString);
                $jsonData = json_decode($jsonString, true);
            }
        }
    }
    
    if ($jsonData) {
        // 使用解析后的JSON数据
        return [
            'chineseDesc' => $jsonData['cn'] ?? '',
            'englishDesc' => $jsonData['en'] ?? '',
            'chineseTags' => $jsonData['cn-tag'] ?? '',
            'englishTags' => $jsonData['en-tag'] ?? '',
            'rawContent' => $aiContent
        ];
    } else {
        // 如果JSON解析失败，返回错误
        return ['error' => '无法解析AI返回的JSON数据'];
    }
}
?>
