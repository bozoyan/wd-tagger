HTML/JavaScript 是在客户端（浏览器）执行的。两者需要通过 HTTP 请求（如表单提交或 AJAX）进行通信。

以下是为您重新设计的、结构正确且功能完整的解决方案。它包含两个核心文件：

index.php: 前端界面和 JavaScript 逻辑，负责收集用户输入、上传文件，并通过 AJAX 将数据发送给后端。

process.php: 后端 PHP 逻辑，负责接收文件、调用智谱AI API、创建文件夹、重命名文件并保存 TXT 结果。