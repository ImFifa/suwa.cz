<?php

namespace App\Model;

use K2D\Core\Models\BaseModel;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class WebinarModel extends BaseModel
{
	protected string $table = 'webinar';
//
//	public function getPublicServices($lang): array
//	{
//		return $this->getTable()->where('public', 1)->where('lang', $lang)->order('id ASC')->fetchAll();
//	}
//
//	public function getService($slug, $lang): ?ActiveRow
//	{
//		return $this->getTable()->where('slug', $slug)->where('lang', $lang)->fetch();
//	}
}
