<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\blitz\models;

use craft\base\Model;
use craft\helpers\UrlHelper;

/**
 * @property-read string $url
 */
class SiteUriModel extends Model
{
    /**
     * @var string|int|null
     */
    public string|int|null $siteId = null;

    /**
     * @var string
     */
    public string $uri = '';

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        // Normalize the URI attribute
        $this->uri = str_replace('__home__', '', $this->uri);
    }

    /**
     * Returns a URL.
     */
    public function getUrl(): string
    {
        return UrlHelper::siteUrl($this->uri, null, null, $this->siteId);
    }
}
