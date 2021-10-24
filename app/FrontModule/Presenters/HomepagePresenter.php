<?php declare(strict_types = 1);

namespace App\FrontModule\Presenters;

use App\Model\ServiceModel;
use Nette\Application\UI\Form;
use Nette\Database\DriverException;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use Nette\Neon\Neon;

class HomepagePresenter extends BasePresenter
{

	/** @inject */
	public ServiceModel $serviceModel;


	public function beforeRender(): void
	{
		parent::beforeRender();
		$vars = $this->configuration->getAllVars();
		$phone = $vars['phone'];
		$email = $vars['email'];
		$this->template->phone = $phone;
		$this->template->email = $email;
	}

	public function renderDefault(): void
	{
		$this->template->services = $this->serviceModel->getPublicServices($this->lang);
	}

	public function renderContact(): void
	{

	}

	protected function createComponentContactForm(): Form
	{
		$form = new Form();

		$form->addText('name', 'Jméno a příjmení')
			->addRule(Form::MAX_LENGTH, 'Maximálné délka je %s znaků', 120)
			->setRequired('Musíte uvést Vaše jméno a příjmení');

		$form->addText('phone', 'Telefonní číslo')
			->setRequired('Musíte uvést telefonní číslo');

		$form->addEmail('email', 'Emailová adresa')
			->addRule(Form::MAX_LENGTH, 'Maximálné délka je %s znaků', 120)
			->setRequired('Musíte uvést Vaši emailovou adresu');

		$form->addTextArea('message', 'Text zprávy')
			->addRule($form::MAX_LENGTH, 'Zpráva je příliš dlouhá', 10000);

		$form->addInvisibleReCaptcha('recaptcha')
			->setMessage('Jste opravdu člověk?');

		$form->addSubmit('submit', 'Odeslat');

		$form->onSubmit[] = function (Form $form) {
			try {
				$values = $form->getValues(true);

				if (!empty($values)) {
					$mail = new Message();

					$vars = $this->configuration->getAllVars();
					if (isset($vars['email']))
						$ownersEmail = $vars['email'];
					else
						$ownersEmail = 'fifa.urban@gmail.com';

					$mail->setFrom($values['email'], $values['name'])
						->addTo($ownersEmail)
						->setSubject('Suwa.cz - a message from contact form')
						->setBody($values['message']);

					$parameters = Neon::decode(file_get_contents(__DIR__ . "/../../config/server/local.neon"));

					$mailer = new SmtpMailer([
						'host' => $parameters['mail']['host'],
						'username' => $parameters['mail']['username'],
						'password' => $parameters['mail']['password'],
						'secure' => $parameters['mail']['secure'],
					]);

					$mailer->send($mail);
				}

				$this->flashMessage('Email byl úspěšně odeslán!');
				$this->redirect('this?odeslano=1');

			} catch (DriverException $e) {
				$this->flashMessage('Vaši zprávu se nepodařilo odeslat. Kontaktujte prosím správce webu na info@filipurban.cz', 'danger');
			}
		};

		return $form;
	}

}
