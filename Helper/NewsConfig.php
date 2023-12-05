<?php
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2023 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\PotNews\Helper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class NewsConfig
 * @package Jajuma\PotNews\Helper
 */
class NewsConfig extends AbstractHelper
{
    public const NEWS_ENABLED = 'power_toys/news/is_enabled';
    public const NEWS_FEED = 'power_toys/news/feed';
    public const NEWS_LIMIT = 'power_toys/news/limit';

    /**
     * @param null $store
     * @return bool
     */
    public function isEnable($store = null)
    {
        return (int) $this->scopeConfig->getValue(
            self::NEWS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getAdditionalFeed($store = null)
    {
        return $this->scopeConfig->getValue(
            self::NEWS_FEED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getLimitNews($store = null)
    {
        return $this->scopeConfig->getValue(
            self::NEWS_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}