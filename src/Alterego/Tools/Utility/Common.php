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
    public static function amount2str($num, $outputKop = false, $isFloat = false): string
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
        //
        list($amount, $kop) = explode('.', sprintf("%015.2f", floatval($num)));

        $out = [];
        if (intval($amount) > 0) {
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
}