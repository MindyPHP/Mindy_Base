<?php

class PingHelper
{
    public $pingSearchEngines = [
        'bing' => "http://www.bing.com/webmaster/ping.aspx?siteMap={sitemap}",
        'ask' => "http://submissions.ask.com/ping?sitemap={sitemap}",
        'google' => "http://www.google.com/webmasters/sitemaps/ping?sitemap={sitemap}",
        'moreover' => "http://api.moreover.com/ping?sitemap={sitemap}",
        'yandex' => "http://webmaster.yandex.ru/wmconsole/sitemap_list.xml?host={sitemap}",
        'yahoo1' => "http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=SitemapWriter&url={sitemap}",
        'yahoo2' => "http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap={sitemap}",
        'live' => "http://webmaster.live.com/ping.aspx?siteMap={sitemap}"
    ];

    public function pingAll($sitemap)
    {
        $result = [];
        foreach ($this->pingSearchEngines as $service) {
            if ($this->ping($sitemap, $service)) {
                $result[] = $service;
            }
        }

        return $result;
    }

    public function ping($sitemap, $service)
    {
        if (!isset($this->pingSearchEngines[$service])) {
            return false;
        }

        $ping = strtr($this->pingSearchEngines[$service], ['{sitemap}' => $sitemap]);
        $success = @file_get_contents($ping);

        return empty($success) === false;
    }
}
