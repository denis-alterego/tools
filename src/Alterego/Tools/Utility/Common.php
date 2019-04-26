<?php

namespace Alterego\Tools\Utility;

class Common
{
    /**
     * Получение суммы прописью
     *
     * @param int|float $num
     * @param bool $outputKop Выводить копейки
     * @param bool $isFloat Выводить как дробное число
     * @return string
     */
    public static function amount2str($num, bool $outputKop = false, bool $isFloat = false): string
    {
        $nul = 'ноль';
        $ten = [
            ['', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
            ['', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
        ];
        $a20 = ['десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'];
        $tens = [2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'];
        $hundred = ['', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'];
        $unit = [
            ['копейка', 'копейки', 'копеек', 1],
            ['рубль', 'рубля', 'рублей', ($isFloat ? 1 : 0)],
            ['тысяча', 'тысячи', 'тысяч', 1],
            ['миллион', 'миллиона', 'миллионов', 0],
            ['миллиард', 'милиарда', 'миллиардов', 0],
        ];
        // получаем целую и дробную часть
        list($amount, $kop) = explode('.', sprintf("%015.2f", floatval($num)));

        $out = [];
        if (intval($amount) > 0) {
            // разбиваем сумму по три части
            foreach (str_split($amount, 3) as $unitKey => $v) {
                if (!intval($v))
                    continue;
                $unitKey = sizeof($unit) - $unitKey - 1;
                $gender = $unit[$unitKey][3];
                list($i1, $i2, $i3) = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2 > 1)
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; # 20-99
                else
                    $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                //
                if ($unitKey > 1)
                    $out[] = self::morph($v, $unit[$unitKey][0], $unit[$unitKey][1], $unit[$unitKey][2]);
            }
        } else {
            $out[] = $nul;
        }
        // если нужно представлять не как сумму, а как число
        if ($isFloat) {
            $out[] = self::morph(intval($amount), 'целая', 'целых', 'целых');

            $kop = 0;
            $dec = explode('.', floatval($num));
            if (sizeof($dec) > 1)
                $kop = array_pop($dec);

            if (intval($kop) > 0) {
                $unit = [
                    ['десятая', 'десятых', 'десятых', 1],
                    ['сотая', 'сотых', 'сотых', 1],
                    ['тысячная', 'тысячных', 'тысячных', 1],
                ];
                $unitKey = (strlen($kop) - 1);
                $gender = $unit[$unitKey][3];
                $kop = str_pad($kop, 3, 0, STR_PAD_LEFT);
                list($i1, $i2, $i3) = array_map('intval', str_split($kop, 1));
                //
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2 > 1)
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; # 20-99
                else
                    $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                //
                $out[] = self::morph($kop, $unit[$unitKey][0], $unit[$unitKey][1], $unit[$unitKey][2]);
            }
        } else {
            $out[] = self::morph(intval($amount), $unit[1][0], $unit[1][1], $unit[1][2]);
            if ($outputKop)
                $out[] = $kop . ' ' . self::morph($kop, $unit[0][0], $unit[0][1], $unit[0][2]);
        }

        return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
    }

    /**
     * Склоняем слово
     *
     * @param int $cnt
     * @param string $one
     * @param string $two
     * @param string $many
     * @return string
     */
    public static function morph(int $cnt, string $one, string $two, string $many): string
    {
        $cnt = abs(intval($cnt)) % 100;
        if ($cnt > 10 && $cnt < 20)
            return $many;

        $cnt = $cnt % 10;
        if ($cnt > 1 && $cnt < 5)
            return $two;

        if ($cnt == 1)
            return $one;

        return $many;
    }

    /**
     * Очищаем телефон от лишних символов
     * делаем начало с цифры 7
     *
     * @param string $phone Непосредственно номер телефона
     * @param string $addPrefix если нужно добавить символ(ы) перед номером, например "+"
     * @return strin очищенная строка
     */
    public static function clearPhone(string $phone = '', $addPrefix = null): string
    {
        // оставляем только цифры
        $resPhone = preg_replace("/[^0-9]/", "", $phone);

        // если номер не полный и он начинается не с 7 или 8
        if (strlen($resPhone) === 10 && $resPhone{0} != 7 && $resPhone{0} != 8)
            $resPhone = '7' . $resPhone;
        // делаем начало всегда с цифры 7
        if (strlen($resPhone) === 11)
            $resPhone = preg_replace("/^(7|8)/", "7", $resPhone);
        // если нужно задать префикс перед номером телефона
        if ($addPrefix)
            $resPhone = $addPrefix . $resPhone;

        return $resPhone;
    }

    /**
     * Делаем первый символ с большой буквы для utf
     * @param string $text Исходный текст
     * @param string $encoding Кодировка
     * @return string
     */
    public static function mbucfirst(string $text, string $encoding = 'utf-8'): string
    {
        if (empty($text)) return '';

        return mb_strtoupper(mb_substr($text, 0, 1, $encoding), $encoding) . mb_substr($text, 1, null, $encoding);
    }
}