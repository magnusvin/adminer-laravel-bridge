<?php

/**
 * vendor/vrana/adminer/plugins/designs.php declares this constructor as
 * accepting `list<string> $designs URL in key, name in value` - but its own
 * body (`array_key_exists($_SESSION["design"], $this->designs)`, `array(...)
 * + $this->designs`) only works with an associative URL => name map, never a
 * sequential list. This stub corrects the documented shape for static
 * analysis without touching vendored code.
 */
class AdminerDesigns
{
    /** @param array<string, string> $designs */
    public function __construct(array $designs) {}
}
