<?php
isset($_GET['id']) ? $id = $_GET['id'] : exit('error');

require '../tools/modelList.php';
require '../tools/modelTextures.php';
require '../tools/jsonCompatible.php';

$modelList = new modelList();
$modelTextures = new modelTextures();
$jsonCompatible = new jsonCompatible();

$id = explode('-', $id);
$modelId = (int)$id[0];
$modelTexturesId = isset($id[1]) ? (int)$id[1] : 0;

$modelName = $modelList->id_to_name($modelId);

if (is_array($modelName)) {
    if ($modelTexturesId > 0) {
        $modelName = $modelName[$modelTexturesId-1];
    } else {
        $modelName = $modelName[0];
    }
    $pathv2 = '../model/'.$modelName.'/index.json';
    $modelMatch = preg_match('/\/[^\/]+$/',$modelName,$v3Match) ? $v3Match[0] : '/'.$modelName;
    $pathv3 = '../model/'.$modelName.$modelMatch.'.model3.json';
    if (file_exists($pathv2)){
        $json = json_decode(file_get_contents($pathv2), 1);
    } elseif (file_exists($pathv3)) {
        $json = json_decode(file_get_contents($pathv3), 1);
    }
    
} else {
    $pathv2 = '../model/'.$modelName.'/index.json';
    $modelMatch = preg_match('/\/[^\/]+$/',$modelName,$v3Match) ? $v3Match[0] : '/'.$modelName;
    $pathv3 = '../model/'.$modelName.$modelMatch.'.model3.json';
    if(file_exists($pathv2)){
        $json = json_decode(file_get_contents($pathv2), 1);
    } elseif (file_exists($pathv3)){
        $json = json_decode(file_get_contents($pathv3), 1);
    }
    
    if ($modelTexturesId > 0) {
        $modelTexturesName = $modelTextures->get_name($modelName, $modelTexturesId);
        if (isset($modelTexturesName)) {
            if (is_array($modelTexturesName)) {
                $json['textures'] = $modelTexturesName;
            } else {
                $json['textures'] = array($modelTexturesName);
            }
        } 
    }
}
if (file_exists($pathv2)) {
    foreach ($json['textures'] as $k => $texture)
	    $json['textures'][$k] = '../model/' . $modelName . '/' . $texture;

    $json['model'] = '../model/'.$modelName.'/'.$json['model'];
    if (isset($json['pose'])) $json['pose'] = '../model/'.$modelName.'/'.$json['pose'];
    if (isset($json['physics'])) $json['physics'] = '../model/'.$modelName.'/'.$json['physics'];

    if (isset($json['motions']))
        foreach ($json['motions'] as $k => $v) foreach($v as $k2 => $v2) foreach ($v2 as $k3 => $motion)
            if ($k3 == 'file' || $k3 == 'sound') $json['motions'][$k][$k2][$k3] = '../model/' . $modelName . '/' . $motion;

    if (isset($json['expressions']))
        foreach ($json['expressions'] as $k => $v) foreach($v as $k2 => $expression)
            if ($k2 == 'file') $json['expressions'][$k][$k2] = '../model/' . $modelName . '/' . $expression;

    header("Content-type: application/json");
    echo $jsonCompatible->json_encode($json);
} elseif (file_exists($pathv3)) {
    $json['FileReferences']['Moc'] = '../model/'.$modelName.'/'.$json['FileReferences']['Moc'];

    foreach ($json['FileReferences']['Textures'] as $k => $texture)
        $json['FileReferences']['Textures'][$k] = '../model/' . $modelName . '/' . $texture;
    
    if (isset($json['FileReferences']['Physics'])) $json['FileReferences']['Physics'] = '../model/'.$modelName.'/'.$json['FileReferences']['Physics'];

    if (isset($json['FileReferences']['Motions']))
        foreach ($json['FileReferences']['Motions'] as $k => $v) foreach($v as $k2 => $v2) foreach ($v2 as $k3 => $motion)
            if ($k3 == 'File' || $k3 == 'Sound') $json['FileReferences']['Motions'][$k][$k2][$k3] = '../model/' . $modelName . '/' . $motion;
    
    header("Content-type: application/json");
    echo $jsonCompatible->json_encode($json);
} else {
    echo "This is not '.json' OR 'model3.json'";
}

