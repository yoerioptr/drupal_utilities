<?php

namespace Drupal\tokeniser\Token;

use Drupal\Component\Render\MarkupInterface;

interface TokenInterface {

  public function getValue(array $data, array $options): MarkupInterface|string|NULL;

}