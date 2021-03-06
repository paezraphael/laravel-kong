<?php

namespace DouglasDC3\Kong\Api;

use DouglasDC3\Kong\Kong;
use DouglasDC3\Kong\Model\Plugin\AclPlugin;
use DouglasDC3\Kong\Model\Plugin\JwtPlugin;
use DouglasDC3\Kong\Model\Plugin\KeyAuthPlugin;
use DouglasDC3\Kong\Model\Plugin\Plugin;

class Plugins extends KongApi
{
    /**
     * @var null
     */
    private $parent;

    private static $mapping = [
        'acl' => AclPlugin::class,
        'jwt' => JwtPlugin::class,
        'key-auth' => KeyAuthPlugin::class,
    ];

    /**
     * Plugins constructor.
     *
     * @param \DouglasDC3\Kong\Kong $kong
     * @param null                  $parent
     */
    public function __construct(Kong $kong, $parent = null)
    {
        parent::__construct($kong);
        $this->parent = $parent;
    }

    /**
     * List all plugins
     *
     * @return static
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function list()
    {
        return collect($this->kong->getClient()->get($this->buildUrl())['data'])
            ->map(function ($item) {
                return $this->mapPlugin($item);
            });
    }

    /**
     * Create a new plugin
     *
     * @param \DouglasDC3\Kong\Model\Plugin\Plugin $plugin
     *
     * @return Plugin
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(Plugin $plugin)
    {
        $plugin->setParent($this->parent);

        $class = get_class($plugin);

        return new $class($this->kong->getClient()->post($this->buildUrl(), $plugin->toArray()));
    }

    /**
     * Update an existing plugin
     *
     * @param \DouglasDC3\Kong\Model\Plugin\Plugin $plugin
     *
     * @return Plugin
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(Plugin $plugin)
    {
        $plugin->setParent($this->parent);

        $class = get_class($plugin);

        return new $class($this->kong->getClient()->put('plugins', $plugin->toArray()));
    }

    /**
     * Delete a plugin
     *
     * @param string $id Uuid
     *
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($id)
    {
        return $this->deleteCall("plugins/$id");
    }

    private function buildUrl()
    {
        if ($this->parent) {
            return $this->parent->getPath() . '/plugins';
        }

        return '/plugins';
    }

    private function mapPlugin($item)
    {
        if (self::$mapping[$item['name']] ?? false) {
            return new self::$mapping[$item['name']]($item);
        }

        return $item;
    }
}
