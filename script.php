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
        if (!containsAnimals($description)) {
            continue;
        }
        if (containsProhibitedWords($description)) {
            continue;
        }
        if (hasMultipleAnimalReferences($description)) {
            continue;
        }
        $moscowCount++;
    }
}

echo "<p>Найдено квартир в Москве с возможностью размещения с животными: $moscowCount\n</p>";

/**
 * Проверяет, содержатся ли слова "животные", "животных" или "с животными" в описании
 *
 * @param string $description Описание квартиры
 * @return bool Возвращает true, если одно из слов найдено, иначе false
 */
function containsAnimals(string $description): bool
{
    return stripos($description, 'животные') !== false
        || stripos($description, 'животных') !== false
        || stripos($description, 'с животными') !== false;
}

/**
 * Проверяет наличие запрещающих слов в описании, находящихся в одном предложении с "животные", "животных" или "с животными"
 *
 * @param string $description Описание квартиры
 * @return bool Возвращает true, если найдено запрещающее слово в одном предложении с одним из указанных слов, иначе false
 */
function containsProhibitedWords(string $description): bool
{
    return preg_match('/(животные|животных|с животными).*?(нельзя|без|не заселяем|не допускается|не разрешается|не разрешено\s*|не сдаётся|невозможно|не предусмотрено|запрещено|не принимаем)|'
            . '(нельзя|без|не заселяем|не допускается|не разрешается|не разрешено\s*|не сдаётся|невозможно|не предусмотрено|запрещено|не принимаем).*?(животные|животных|с животными)/i', $description) > 0;
}

/**
 * Проверяет, содержатся ли слова "животные", "животных" или "с животными" более одного раза в описании
 *
 * @param string $description Описание квартиры
 * @return bool Возвращает true, если упоминания больше одного, иначе false
 */
function hasMultipleAnimalReferences(string $description): bool
{
    return preg_match_all('/животные|животных|с животными/i', $description) > 1;
}