<?php declare(strict_types = 1);

namespace App\FrontModule\Presenters;

use App\Model\ServiceModel;
use K2D\Gallery\Models\GalleryModel;
use K2D\Gallery\Models\ImageModel;

class ServicePresenter extends BasePresenter
{

	/** @inject */
	public ServiceModel $serviceModel;

	/** @inject */
	public GalleryModel $galleryModel;

	/** @inject */
	public ImageModel $imageModel;

	public function renderDefault(): void
	{
		$this->template->services = $this->serviceModel->getPublicServices($this->lang);
	}

	public function renderDetail($slug): void
	{
		$service = $this->serviceModel->getService($slug, $this->lang);

		// get images
		if ($service->gallery_id != NULL)
			$this->template->images = $this->imageModel->getImagesByGallery($service->gallery_id);


		$this->template->service = $service;
	}
}
