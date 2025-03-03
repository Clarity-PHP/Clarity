<?php

namespace framework\clarity\Http\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Csrf
{

}