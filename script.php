<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '10G');

$localFilePath = '/var/www/html/BrainForce/yrl_searchapp.xml';

if (!file_exists($localFilePath)) {
    die("Файл не найден: $localFilePath\n");
}

$xmlContent = file_get_contents($localFilePath);

//-------------------------------- Квартиры в Ярославле --------------------------------------//

$yaroslavlCount = 0;

$pattern = '/<locality-name>Ярославль<\/locality-name>.*?<type>аренда<\/type>.*?<category>flat<\/category>/s';

if (preg_match_all($pattern, $xmlContent, $matches)) {
    $yaroslavlCount += count($matches[0]);
}

echo "<p>Найдено квартир, которые сдаются в Ярославле: $yaroslavlCount\n</p>";


//----------------------------------- Квартиры в Минске ----------------------------------------//

$pattern = '/<locality-name>Минск<\/locality-name>.*?<type>аренда<\/type>.*?<category>flat<\/category>.*?<price>.*?<value>(.*?)<\/value>.*?<\/price>/s';

if (preg_match_all($pattern, $xmlContent, $matches)) {
    $prices = array_map('floatval', $matches[1]);

    sort($prices);
    $count = count($prices);

    if ($count > 0) {
        $median = ($count % 2 == 0)
            ? ($prices[$count / 2 - 1] + $prices[$count / 2]) / 2
            : $prices[floor($count / 2)];

        echo "<p>Медианная цена аренды квартиры в Минске: " . round($median) . " RUB\n<p/>";
    } else {
        echo "<p>Не найдено цен для расчета медианы.\n</p>";
    }
} else {
    echo "<p>Не найдено объявлений об аренде квартир в Минске.\n</p>";
}

//-------------------------------------Квартиры в Москве-------------------------------------------//

$moscowCount = 0;

$pattern = '/<locality-name>Москва<\/locality-name>.*?<type>аренда<\/type>.*?<category>flat<\/category>.*?<description>(.*?)<\/description>/s';

if (preg_match_all($pattern, $xmlContent, $matches)) {
    foreach ($matches[1] as $description) {
        if (stripos($description, 'животные') !== false || stripos($description, 'с животными') !== false) {
            $moscowCount++;
        }
    }
}

echo "<p>Найдено квартир в Москве с возможностью размещения с животными: $moscowCount\n</p>";
