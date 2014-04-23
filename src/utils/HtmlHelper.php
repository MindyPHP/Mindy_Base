<?php

/**
 * Created by Studio107.
 * Date: 26.04.13
 * Time: 12:50
 * All rights reserved.
 */
class HtmlHelper
{
    /**
     * Remove html comments
     * @param $html
     * @return mixed
     */
    public static function removeComments($html)
    {
        return preg_replace('/<!--(.*)-->/', '', (string)$html);
    }

    /**
     * Remove duble spaces
     * @param $html
     * @return mixed
     */
    public static function removeSpaces($html)
    {
        return preg_replace('/[ |	]{2,}/m', ' ', (string)$html);
    }

    /**
     * Return body for email from html page
     * @param $html
     * @return string
     */
    public static function clearEmailHtml($html)
    {
        $html = self::removeSpaces($html);
        return trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s', '', (string)$html)));
    }
}
