<?php

use Tempest\Cache\Config\FilesystemCacheConfig;

use function Tempest\root_path;

return new FilesystemCacheConfig(
    directory: root_path('.tempest/advocacy-cache'),
    tag: 'advocacy',
);
