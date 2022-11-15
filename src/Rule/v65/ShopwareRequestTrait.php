<?php

namespace Frosh\Rector\Rule\v65;

use PhpParser\Builder\Class_;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

trait ShopwareRequestTrait
{
    protected array $ATTRIBUTES_REQUEST = [
        'PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID' => 'saleChannelId',
        'RequestTransformer::SALES_CHANNEL_BASE_URL' => 'saleChannelBaseUrl',
        'RequestTransformer::SALES_CHANNEL_ABSOLUTE_BASE_URL' => 'saleChannelAbsoluteBaseUrl',
        'RequestTransformer::STOREFRONT_URL' => 'storefrontUrl',
        'RequestTransformer::SALES_CHANNEL_RESOLVED_URI' => 'saleChannelResolvedUri',
        'RequestTransformer::ORIGINAL_REQUEST_URI' => 'originalRequestUri',
        'SalesChannelRequest::ATTRIBUTE_IS_SALES_CHANNEL_REQUEST' => 'isSaleChannelRequest',
        'SalesChannelRequest::ATTRIBUTE_DOMAIN_LOCALE' => 'domainLocale',
        'SalesChannelRequest::ATTRIBUTE_DOMAIN_SNIPPET_SET_ID' => 'domainSnippetSetId',
        'SalesChannelRequest::ATTRIBUTE_DOMAIN_CURRENCY_ID' => 'domainCurrencyId',
        'SalesChannelRequest::ATTRIBUTE_DOMAIN_ID' => 'domainId',
        'SalesChannelRequest::ATTRIBUTE_THEME_ID' => 'themeId',
        'SalesChannelRequest::ATTRIBUTE_THEME_NAME' => 'themeName',
        'SalesChannelRequest::ATTRIBUTE_THEME_BASE_NAME' => 'themeBaseName',
        'SalesChannelRequest::ATTRIBUTE_SALES_CHANNEL_MAINTENANCE' => 'saleChannelMaintenance',
        'SalesChannelRequest::ATTRIBUTE_SALES_CHANNEL_MAINTENANCE_IP_WHITLELIST' => 'saleChannelMaintenanceIpWhitelist',
        'SalesChannelRequest::ATTRIBUTE_CANONICAL_LINK' => 'canonicalLink',
    ];

    protected function havingSwRequestTransformed(MethodCall $method): ?Variable
    {
        if (!$this->isName($method->name, 'get')) {
            return null;
        }

        /** @var PropertyFetch $requestAttributes */
        $requestAttributes = $method->var;
        if (!$this->isObjectType($requestAttributes, new ObjectType('Symfony\\Component\\HttpFoundation\\ParameterBag')) || !$this->isName($requestAttributes, 'attributes')) {
            return null;
        }

        /** @var Variable $caller */
        $caller = $method->var->var ?? null;
        if (!$caller instanceof Variable || !$this->isObjectType($caller, new ObjectType('Symfony\\Component\\HttpFoundation\\Request'))) {
            return null;
        }

        /** @var Node|ClassConstFetch $arg1 */
        $arg1 = $method->args[0]->value;
        if (!$arg1 instanceof ClassConstFetch || $this->getTransformedShopwareAttr($arg1) === '') {
            return null;
        }

        return $caller;
    }

    protected function classConstToString(ClassConstFetch $arg): string
    {
        return sprintf('%s::%s', $arg->class, $arg->name);
    }

    protected function getTransformedShopwareAttr($attr): string
    {
        $const = $this->classConstToString($attr);
        if (\array_key_exists($const, $this->ATTRIBUTES_REQUEST)) {
            return $this->ATTRIBUTES_REQUEST[$const];
        }

        $const = str_replace('Shopware\\Core\\', '', $const);
        if (\array_key_exists($const, $this->ATTRIBUTES_REQUEST)) {
            return $this->ATTRIBUTES_REQUEST[$const];
        }

        return '';
    }
}
