<?php

namespace App\Enums;

enum CollectionAccessLevel: string
{
  case Private = 'private';
  case Public = 'public';
  case Restrict = 'shared';
}