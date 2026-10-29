<?php

function media(\Shopware\Core\Content\Media\Extension\ResolveRemoteThumbnailUrlExtension $event, $path, $date): void
{
    $event->mediaPath = $path;
    $event->mediaUpdatedAt = $date;
    $value = $event->mediaPath = $path;
}
-----
<?php

function media(\Shopware\Core\Content\Media\Extension\ResolveRemoteThumbnailUrlExtension $event, $path, $date): void
{
    $event->mediaEntity->assign(['path' => $path]);
    $event->mediaEntity->assign(['updatedAt' => $date]);
    $value = $event->mediaPath = $path;
}
