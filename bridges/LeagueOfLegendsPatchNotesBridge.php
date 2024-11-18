<?php

class LeagueOfLegendsPatchNotesBridge extends BridgeAbstract
{
    const NAME = 'League of Legends Patch Notes';
    const URI = 'https://www.leagueoflegends.com/';
    const DESCRIPTION = 'League of Legends Patch Notes newsfeed';
    const MAINTAINER = 'ansg191';
    const PARAMETERS = [
        '' => [
            'locale' => [
                'name' => 'Language',
                'type' => 'list',
                'values' => [
                    'العربية' => 'ar-ae',
                    'čeština' => 'cs-cz',
                    'Deutsch' => 'de-de',
                    'Ελληνικά' => 'el-gr',
                    'English (AU)' => 'en-au',
                    'English (EU)' => 'en-gb',
                    'English (US)' => 'en-us',
                    'Español (EU)' => 'es-es',
                    'Español (AL)' => 'es-mx',
                    'Français' => 'fr-fr',
                    'magyar' => 'hu-hu',
                    'Italiano' => 'it-it',
                    '日本語' => 'ja-jp',
                    '한국어' => 'ko-kr',
                    'Polski' => 'pl-pl',
                    'română' => 'ro-ro',
                    'Português (AL)' => 'pt-br',
                    'Русский' => 'ru-ru',
                    'ภาษาไทย' => 'th-th',
                    'Türkçe' => 'tr-tr',
//                    '简体中文' => 'zh-cn',
                    '繁體中文' => 'zh-tw'
                ],
                'defaultValue' => 'en-us',
                'title' => 'Select your language'
            ]
        ]
    ];
    const CACHE_TIMEOUT = 3600;

    public function getIcon()
    {
        return <<<icon
https://cmsassets.rgpub.io/sanity/images/dsfx7636/content_organization_live/c0ff1c73fab6ddc82472ff5c79c9bd5a0032c616-110x70.svg
icon;
    }

    public function collectData()
    {
        $url = sprintf('https://www.leagueoflegends.com/%s/news/tags/patch-notes/', $this->getInput('locale'));
        $dom = getSimpleHTMLDOMCached($url);
        $dom = $dom->find('section[id=news]', 0);
        if (!$dom) {
            throw new \Exception(sprintf('Unable to find css selector on `%s`', $url));
        }
        $dom = defaultLinkTo($dom, $this->getURI());

        foreach ($dom->find('a[data-testid="articlefeaturedcard-component"]') as $article) {
            $title = $article->find('div[data-testid="card-title"]', 0)->plaintext;
            $info = $this->getArticle($article->href);
            $this->items[] = [
                'title' => $title,
                'uri' => $article->href,
                'author' => $info->author,
                'content' => $info->content,
                'timestamp' => strtotime($article->find('time', 0)->datetime),
                'enclosures' => [$info->banner]
            ];
        }
    }

    /**
     * Loads the patch notes page to get author, content, and banner image.
     *
     * @param $url string
     * @return object
     */
    private function getArticle($url)
    {
        $dom = getSimpleHTMLDOMCached($url);
        $dom = defaultLinkTo($dom, $this->getURI());

        $author = '';
        $content = $dom->find('section[data-testid="RichTextPatchNotesBlade"]', 0);
        $banner = '';

        $authorEl = $dom->find('div[class="authors"] > span', 0);
        if ($authorEl) {
            $author = $authorEl->plaintext;
        }

        $bannerEl = $dom->find('meta[property="og:image"]', 0);
        if ($bannerEl) {
            $banner = $bannerEl->content;
        }

        return (object)[
            'author' => $author,
            'content' => $content,
            'banner' => $banner,
        ];
    }
}
