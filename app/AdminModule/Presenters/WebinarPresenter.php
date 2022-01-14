<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Grid\WebinarGridFactory;
use App\AdminModule\Grid\WebinarGrid;
use App\Model\WebinarModel;
use K2D\Core\AdminModule\Component\CropperComponent\CropperComponent;
use K2D\Core\AdminModule\Component\CropperComponent\CropperComponentFactory;
use K2D\Core\AdminModule\Presenter\BasePresenter;
use K2D\Core\Helper\Helper;
use K2D\File\AdminModule\Component\DropzoneComponent\DropzoneComponent;
use K2D\File\AdminModule\Component\DropzoneComponent\DropzoneComponentFactory;
use K2D\Gallery\Models\GalleryModel;
use K2D\Gallery\Models\ImageModel;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;
use Nette\Utils\Image;
use Nette\Utils\Strings;


/**
 * @property-read ActiveRow|null $webinar
 */
class WebinarPresenter extends BasePresenter
{
	/** @inject */
	public WebinarModel $webinarModel;

	/** @inject */
	public GalleryModel $galleries;

	/** @inject */
	public ImageModel $images;

	/** @var WebinarGridFactory @inject */
	public $webinarGridFactory;

	/** @inject */
	public DropzoneComponentFactory $dropzoneComponentFactory;

	/** @inject */
	public CropperComponentFactory $cropperComponentFactory;

	public function renderEdit(?int $id = null): void
	{
		$this->template->webinar = null;

		if ($id !== null && $this->webinar !== null) {
			$webinar = $this->webinar->toArray();

			$form = $this['editForm'];
			$form->setDefaults($webinar);

			$this->template->webinar = $this->webinar;
		}
	}

	public function createComponentEditForm(): Form
	{
		$form = new Form();

		$form->addText('name', 'Webinar name:')
			->addRule(Form::MAX_LENGTH, 'Maximal lenght is %s characters', 50)
			->setRequired('Webinar name is mandatory');

		if ($this->configuration->getLanguagesCount() > 1) {
			$form->addSelect('lang', 'Language:')
				->setItems($this->configuration->languages, false);
		}

		$form->addCheckbox('public', 'Make public')
			->setDefaultValue(true);

		$form->addSelect('gallery_id', 'Attach gallery:')
			->setPrompt('None')
			->setItems($this->galleries->getForSelect());

		$form->addTextArea('description', 'Description', 100, 20)
			->setHtmlAttribute('class', 'form-wysiwyg');

		$form->addSubmit('save', 'Save');

		$form->onSubmit[] = function (Form $form) {
			$values = $form->getValues(true);
			$values['slug'] = Strings::webalize($values['name']);
			$webinar = $this->webinar;

			if ($webinar === null) {
				$webinar = $this->webinarModel->insert($values);
				$this->flashMessage('Webinar created');
			} else {
				$webinar->update($values);
				$this->flashMessage('Webinar edited');
			}

			$this->redirect('this', ['id' => $webinar->id]);
		};

		return $form;
	}

	public function handleUploadFiles(): void
	{
		$fileUploads = $this->getHttpRequest()->getFiles();
		$fileUpload = reset($fileUploads);

		if (!($fileUpload instanceof FileUpload)) {
			return;
		}

		if ($fileUpload->isOk() && $fileUpload->isImage()) {
			$image = $fileUpload->toImage();
			$link = WWW . '/upload/webinars/' . $this->webinar->id . '/';
			$fileName = Helper::generateFileName($fileUpload);

			if (!file_exists($link)) {
				Helper::mkdir($link);
			}

			if ($image->getHeight() > 600 || $image->getWidth() > 800) {
				$image->resize(800, 600);
			}

			$image->save($link . $fileName);
			$this->webinar->update(['cover' => $fileName]);
		}
	}

	public function handleRedrawFiles(): void
	{
		$this->redirect('this');
	}

	public function handleCropImage(): void
	{
		$this->showModal('cropper');
	}

	public function handleDeleteImage(): void
	{
		unlink(WWW . '/upload/webinars/' . $this->webinar->id . '/' . $this->webinar->cover);
		$this->webinar->update(['cover' => null]);
		$this->flashMessage('Cover image was deleted');
		$this->redirect('this');
	}

	public function handleRotateLeft(string $slug): void
	{
		$this->rotateImage($slug, 90);
		$this->redrawControl('image');
	}

	public function handleRotateRight(string $slug): void
	{
		$this->rotateImage($slug, -90);
		$this->redrawControl('image');
	}

	protected function createComponentWebinarGrid(): WebinarGrid
	{
		return $this->webinarGridFactory->create();
	}

	protected function createComponentDropzone(): DropzoneComponent
	{
		$control = $this->dropzoneComponentFactory->create();
		$control->setPrompt('Upload an image by dragging it or clicking here.');
		$control->setUploadLink($this->link('uploadFiles!'));
		$control->setRedrawLink($this->link('redrawFiles!'));

		return $control;
	}

	protected function createComponentCropper(): CropperComponent
	{
		$cropper = $this->cropperComponentFactory->create();

		if ($this->webinar->cover !== null) {
			$cropper->setImagePath('upload/webinars/' . $this->webinar->id . '/' . $this->webinar->cover)
				->setAspectRatio((float) $this->configuration->img_resize);
		}

		$cropper->onCrop[] = function (): void {
			$this->redirect('this');
		};

		return $cropper;
	}

	protected function getWebinar(): ?ActiveRow
	{
		return $this->webinarModel->get($this->getParameter('id'));
	}

	private function rotateImage(string $slug, int $angle): void
	{
		$webinar = $this->webinarModel->getWebinar($slug);
		$imageOriginalPath = WWW . '/upload/webinars/' . $webinar->id . '/' . $webinar->cover;
		$imageOriginal = Image::fromFile($imageOriginalPath);
		$imageOriginal->rotate($angle, 0)->save($imageOriginalPath);
	}
}
