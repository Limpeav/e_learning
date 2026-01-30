<?php
require_once 'config/db.php';

// Fetch all lessons with course information
$stmt = $pdo->query("
    SELECT l.id, l.title, l.type, l.content, c.title as course_title 
    FROM lessons l 
    JOIN courses c ON l.course_id = c.id 
    ORDER BY c.id, l.id
");
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create Word-compatible HTML document
$html = '
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>E-Learning Lesson Documentation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 1in;
        }
        h1 {
            color: #003366;
            font-size: 24pt;
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            color: #336699;
            font-size: 18pt;
            margin-top: 30px;
            border-bottom: 2px solid #336699;
            padding-bottom: 5px;
        }
        h3 {
            color: #336699;
            font-size: 14pt;
            margin-top: 20px;
        }
        p {
            text-align: justify;
            margin: 10px 0;
        }
        .lesson-info {
            background-color: #f0f0f0;
            padding: 10px;
            margin: 15px 0;
            border-left: 4px solid #336699;
        }
        .lesson-content {
            margin: 15px 0;
            padding: 10px;
        }
        ul {
            margin: 10px 0;
            padding-left: 30px;
        }
        li {
            margin: 5px 0;
        }
        .page-break {
            page-break-after: always;
        }
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        code {
            background-color: #f4f4f4;
            padding: 2px 6px;
            font-family: "Courier New", monospace;
        }
        pre {
            background-color: #f4f4f4;
            padding: 15px;
            border: 1px solid #ddd;
            overflow-x: auto;
            font-family: "Courier New", monospace;
        }
    </style>
</head>
<body>
';

// Title Page
$html .= '
    <div class="center">
        <h1>E-Learning System</h1>
        <h2>Lesson Documentation</h2>
        <p><i>Generated: ' . date('F j, Y') . '</i></p>
    </div>
    <div class="page-break"></div>
';

// Table of Contents
$html .= '
    <h2>Table of Contents</h2>
    <ul>
';

$lessonNumber = 1;
foreach ($lessons as $lesson) {
    $html .= '<li><b>Lesson ' . $lessonNumber . ':</b> ' . htmlspecialchars($lesson['title']) . '</li>';
    $lessonNumber++;
}

$html .= '
    </ul>
    <div class="page-break"></div>
';

// Generate each lesson
$lessonNumber = 1;
foreach ($lessons as $lesson) {
    $html .= '<h2>Lesson ' . $lessonNumber . ': ' . htmlspecialchars($lesson['title']) . '</h2>';
    
    // Lesson Info Box
    $html .= '
        <div class="lesson-info">
            <p><b>Course:</b> ' . htmlspecialchars($lesson['course_title']) . '</p>
            <p><b>Lesson Type:</b> ' . ucfirst($lesson['type']) . '</p>
        </div>
    ';
    
    // Lesson Content
    $html .= '<h3>Lesson Content</h3>';
    $html .= '<div class="lesson-content">';
    
    // The content might have HTML tags from the editor
    $html .= $lesson['content'];
    
    $html .= '</div>';
    
    // Add page break between lessons (except for the last one)
    if ($lessonNumber < count($lessons)) {
        $html .= '<div class="page-break"></div>';
    }
    
    $lessonNumber++;
}

// Footer
$html .= '
    <br><br>
    <div class="center">
        <p><i>--- End of Documentation ---</i></p>
    </div>
';

$html .= '
</body>
</html>
';

// Save the file
$filename = 'Lesson_Documentation.doc';
$filepath = __DIR__ . '/' . $filename;

file_put_contents($filepath, $html);

echo '<!DOCTYPE html>
<html>
<head>
    <title>Document Generated</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .success-icon {
            font-size: 60px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        .download-btn {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .download-btn:hover {
            background: #45a049;
        }
        .info {
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✓</div>
        <h1>Document Generated Successfully!</h1>
        <p>Your lesson documentation has been created and is ready to download.</p>
        <p><strong>Total Lessons:</strong> ' . count($lessons) . '</p>
        <a href="' . $filename . '" class="download-btn" download>📥 Download Word Document</a>
        <div class="info">
            <p>This document can be opened and edited in Microsoft Word.</p>
            <p>You can add bold text, change alignment, and create lists.</p>
        </div>
    </div>
</body>
</html>';
?>
