<?php declare(strict_types=1);

namespace App\AdminModule\Grid;

use App\Model\WebinarModel;
use K2D\Core\AdminModule\Grid\BaseV2Grid;
use K2D\Core\Models\ConfigurationModel;
use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Container;

class WebinarGrid extends BaseV2Grid
{


	/** @var WebinarModel */
	private WebinarModel $webinarModel;

	public ConfigurationModel $configuration;

	public function __construct(WebinarModel $webinarModel)
	{
		parent::__construct();
		$this->webinarModel = $webinarModel;
	}

	protected function build(): void
	{
		$this->model = $this->webinarModel;

		parent::build();

		$this->setDefaultOrderBy('created', true);
		$this->setFilterFactory([$this, 'gridFilterFactory']);

		$this->addColumn('name', 'Name');
		$this->addColumn('date', 'Date');
		$this->addColumn('link', 'Link');
		$this->addColumn('public', 'Public');
		$this->addColumn('updated', 'Last updated')->setSortable();
		$this->addColumn('created', 'Created')->setSortable();

		$this->addRowAction('edit', 'Edit', static function (): void {});
		$this->addRowAction('delete', 'Delete', static function (ActiveRow $record): void {
			if ($record->cover) {
				unlink(WWW . '/upload/webinars/' . $record->id . '/' . $record->cover);
			}

			$record->delete();
		})
			->setProtected(false)
			->setConfirmation('Are you sure you want to delete this webinar?');
	}

	public function gridFilterFactory(Container $c): void
	{
		$c->addText('name', 'Webinar name')->setHtmlAttribute('placeholder', 'Filter by webinar name');
	}
}
