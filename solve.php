<?php
// solve.php - 纯净计算版 (配合前端 Cropper 使用)
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *"); 
set_time_limit(60); 

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('仅支持POST请求');
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) throw new Exception('图片接收失败');

    $n = isset($_POST['n']) ? (int)$_POST['n'] : 10;
    if ($n < 5 || $n > 30) throw new Exception('N值超出范围');

    $tmpName = $_FILES['image']['tmp_name'];
    $img = @imagecreatefrompng($tmpName); // 前端传过来的固定是 PNG Blob
    if (!$img) throw new Exception('无法解析裁剪后的图像数据');

    $width = imagesx($img);
    $height = imagesy($img);
    $cellW = $width / $n;
    $cellH = $height / $n;

    $matrix = [];
    $colors = [];
    $tolerance = 30; 

    // 智能取色 (自带避开缝隙线逻辑)
    for ($r = 0; $r < $n; $r++) {
        $matrix[$r] = [];
        for ($c = 0; $c < $n; $c++) {
            $cx = (int)(($c + 0.5) * $cellW);
            $cy = (int)(($r + 0.5) * $cellH);
            $offset = (int)(min($cellW, $cellH) * 0.25);
            
            // 取中心点和四周，防止刚好落在网格白线上
            $points = [
                [$cx, $cy], [$cx, $cy - $offset], [$cx, $cy + $offset], [$cx - $offset, $cy], [$cx + $offset, $cy]
            ];
            
            $bestR = 255; $bestG = 255; $bestB = 255;
            foreach ($points as $pt) {
                $nx = (int)max(0, min($pt[0], $width - 1));
                $ny = (int)max(0, min($pt[1], $height - 1));
                $rgb = imagecolorat($img, $nx, $ny);
                $r_val = ($rgb >> 16) & 0xFF; $g_val = ($rgb >> 8) & 0xFF; $b_val = $rgb & 0xFF;
                
                // 忽略白色/灰色的网格边框线
                if (!($r_val > 220 && $g_val > 220 && $b_val > 220)) {
                    $bestR = $r_val; $bestG = $g_val; $bestB = $b_val; break; 
                }
            }

            // 颜色聚类
            $colorId = -1;
            foreach ($colors as $id => $col) {
                $dist = sqrt(pow($col['r'] - $bestR, 2) + pow($col['g'] - $bestG, 2) + pow($col['b'] - $bestB, 2));
                if ($dist < $tolerance) { $colorId = $id; break; }
            }
            if ($colorId === -1) {
                $colorId = count($colors);
                $colors[] = ['r' => $bestR, 'g' => $bestG, 'b' => $bestB];
            }
            $matrix[$r][$c] = $colorId;
        }
    }
    imagedestroy($img);

    // 回溯求解核心
    $queens = [];
    $solution = null;

    function canPlace($r, $c, &$queens, &$matrix) {
        foreach ($queens as $q) {
            if ($q[0] == $r || $q[1] == $c) return false;
            if (abs($q[0] - $r) <= 1 && abs($q[1] - $c) <= 1) return false;
        }
        $color = $matrix[$r][$c];
        foreach ($queens as $q) {
            if ($matrix[$q[0]][$q[1]] == $color) return false;
        }
        return true;
    }

    function solve($row, $n, &$queens, &$matrix, &$solution) {
        if ($row == $n) { $solution = $queens; return true; }
        for ($col = 0; $col < $n; $col++) {
            if (canPlace($row, $col, $queens, $matrix)) {
                $queens[] = [$row, $col]; 
                if (solve($row + 1, $n, $queens, $matrix, $solution)) return true;
                array_pop($queens); 
            }
        }
        return false;
    }

    solve(0, $n, $queens, $matrix, $solution);

    if ($solution !== null) {
        echo json_encode(['success' => true, 'solution' => $solution]);
    } else {
        echo json_encode(['error' => '无解！程序识别到了 ' . count($colors) . ' 种颜色。如果数字不对，请确认截图是否干净，并且 N 的数值是否填写正确。']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => '执行错误: ' . $e->getMessage()]);
}
?>
