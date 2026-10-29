<?php

class Loader extends \Shopware\Core\System\Snippet\Service\AbstractTranslationLoader
{
    public function pluginTranslationExists(\Shopware\Core\Framework\Plugin $plugin): bool
    {
        return $this->getDecorated()->pluginTranslationExists($plugin);
    }
}
-----
<?php

class Loader extends \Shopware\Core\System\Snippet\Service\AbstractTranslationLoader
{
    public function pluginTranslationExistsForLocale(\Shopware\Core\Framework\Plugin $plugin, string $locale): bool
    {
        return $this->getDecorated()->pluginTranslationExistsForLocale($plugin, locale: $locale);
    }
}
