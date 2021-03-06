<?php

namespace App\Forms;

use Nette\Application\UI\Form;
use Thunbolt\User\BadPasswordException;
use Thunbolt\User\Interfaces\ISignInForm;
use Thunbolt\User\UserNotFoundException;
use Thunbolt\User\User;

final class SignInForm extends BaseForm implements ISignInForm {

	/** @var User */
	private $user;

	public function __construct(User $user) {
		$this->user = $user;
	}

	public function createSignIn(): Form {
		$form = $this->create();

		$form->addText('name', 'core.user.email')
			->setRequired()
			->addRule($form::EMAIL);

		$form->addPassword('password', 'core.user.password')
			->setRequired();

		$form->addCheckbox('remember', 'core.user.remember');

		$form->addSubmit('send', 'core.user.signIn');
		$form->onSuccess[] = [$this, 'successSignIn'];

		return $form;
	}

	public function successSignIn(Form $form, array $values): void {
		if ($values['remember']) {
			$this->user->setExpiration('14 days');
		} else {
			$this->user->setExpiration('20 minutes');
		}

		try {
			$this->user->login($values['name'], $values['password']);
		} catch (BadPasswordException $e) {
			$form->addError($form->getTranslator()->translate('core.user.wrongPassword'));
		} catch (UserNotFoundException $e) {
			$form->addError($form->getTranslator()->translate('core.user.notExists'));
		}
	}

}
