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
    $systemPrompt = "请提供这些图片的中英文详细描述，这些描述信息将用于AI绘画的prompt，中文描述用两个五角星'★★中文描述★★'符号一起包围，英文描述用两个方块'■■英文描述■■'符号一起包围。最后再将中英文的prompt描述内容简化成tag标签。中文Tag标签用中文大写书括号'【中文Tag标签】'符号一起包围，英文Tag标签用英文小写书括号'[英文Tag标签]'符号一起包围，中英文的每个tag标签在包围符号内用英文逗号分割。最后返回的四段信息分别是：中文详细信息（用于AI绘画的Prompt）、 英文详细信息（用于AI绘画的Prompt）、中文Tag标签、英文Tag标签。不要出现 markdown 标签，也不要出现注释信息。";
    
    $fullPrompt = $customPrompt ? $customPrompt . "\n" . $systemPrompt : $systemPrompt;
    
    // 准备API请求
    $url = "https://open.bigmodel.cn/api/paas/v4/chat/completions";
    $data = [
        "model" => "glm-4v",
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
    
    // 使用正则表达式提取各部分内容
    $chineseDesc = '';
    $englishDesc = '';
    $chineseTags = '';
    $englishTags = '';
    
    // 提取中文描述
    if (preg_match('/★★中文描述★★\s*([\s\S]*?)(?=■■英文描述■■|$)/', $aiContent, $matches)) {
        $chineseDesc = trim($matches[1]);
    }
    
    // 提取英文描述
    if (preg_match('/■■英文描述■■\s*([\s\S]*?)(?=【中文Tag标签】|$)/', $aiContent, $matches)) {
        $englishDesc = trim($matches[1]);
    }
    
    // 提取中文标签
    if (preg_match('/【中文Tag标签】\s*([^\]]*?)(?=\[英文Tag标签\]|$)/', $aiContent, $matches)) {
        $chineseTags = trim($matches[1]);
    }
    
    // 提取英文标签
    if (preg_match('/\[英文Tag标签\]\s*([^\]]*?)(?=$)/', $aiContent, $matches)) {
        $englishTags = trim($matches[1]);
    }
    
    return [
        'chineseDesc' => $chineseDesc,
        'englishDesc' => $englishDesc,
        'chineseTags' => $chineseTags,
        'englishTags' => $englishTags,
        'rawContent' => $aiContent
    ];
}
?>
