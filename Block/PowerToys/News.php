<?php
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2023 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\PotNews\Block\PowerToys;
use Jajuma\PowerToys\Block\PowerToys\Dashboard;
use Magento\Framework\View\Element\Template\Context;
use Jajuma\PotNews\Helper\NewsConfig;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\Resolver;

class News extends Dashboard
{
    private $newsConfig;

    private $storeManager;

    private $store;

    protected $listNews = [];

    public function __construct(
        NewsConfig $newsConfig,
        StoreManagerInterface $storeManager,
        Resolver $store,
        Context $context,
        array $data = []
    ) {
        $this->newsConfig = $newsConfig;
        $this->storeManager = $storeManager;
        $this->store = $store;  
        parent::__construct($context, $data);
    }

    public function isEnable(): bool
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->newsConfig->isEnable($storeId);
    }

    public function getNews()
    {
        $urlsFeed = [ 
            'https://mage-os.org/feed/',
            'https://www.hyva.io/blog/feed/index/type/latest_posts/store_id/1/',
            'https://www.jajuma.de/en/blog/rss/feed'
        ];
        $additionalFeed = $this->newsConfig->getAdditionalFeed();
        if ($additionalFeed) {
            $urlsFeed = array_merge($urlsFeed, explode(',', $additionalFeed));
        }
        foreach ($urlsFeed as $url) {
            $this->getNewsFeedFromUrl(trim($url));
        }
        usort($this->listNews, function($a, $b) {
            return strtotime($a['pubDate'])-strtotime($b['pubDate']);
        });

        $listNews = array_reverse($this->listNews);
        $limitNews = (int) $this->newsConfig->getLimitNews();
        if ($limitNews) {
            $listNews = array_slice($listNews, 0, $limitNews);
        }
        return $listNews;
    }

    public function getNewsFeedFromUrl($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        try {
            $doc = new \SimpleXmlElement($data, LIBXML_NOCDATA);
            curl_close($ch);

            if(isset($doc->channel))
            {
                $this->parseRSS($doc);
            }
            if(isset($doc->entry))
            {
                $this->parseAtom($doc);
            }
        } catch (\Exception $e) {

        }
    }

    public function parseRSS($xml)
    {
        //$list['title'] = $xml->channel->title->__toString();
        $cnt = count($xml->channel->item);
        for($i=0; $i<$cnt; $i++)
        {
            $item = [];
            $item['url'] 	= $xml->channel->item[$i]->link->__toString();
            $item['title'] 	= $xml->channel->item[$i]->title->__toString();
            $item['description'] = $xml->channel->item[$i]->description->__toString();
            $item['pubDate'] = $xml->channel->item[$i]->pubDate->__toString();
            $this->listNews []= $item;
        }
    }

    function parseAtom($xml)
    {
        //$list['title'] = $xml->author->name->__toString();
        $cnt = count($xml->entry);
        for($i=0; $i<$cnt; $i++)
        {
            $item = [];
            $urlAtt = $xml->entry->link[$i]->attributes();
            $item['url'] 	= $urlAtt['href'];
            $item['title']  	= $xml->entry->title;
            $item['description']	= strip_tags($xml->entry->content);
            $item['pubDate'] = $xml->channel->item[$i]->pubDate->__toString();
            $this->listNews []= $item;
        }
    }
}