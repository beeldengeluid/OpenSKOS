<?php

class Dashboard_UsersController extends OpenSKOS_Controller_Dashboard
{
	public function indexAction()
	{
		$this->view->users = $this->_tenant->findDependentRowset('OpenSKOS_Db_Table_Users');
	}
	
	public function editAction()
	{
		$this->view->assign('user', $this->_getUser());
	}

	public function saveAction()
	{
		if (!$this->getRequest()->isPost()) {
			$this->getHelper('FlashMessenger')->setNamespace('error')->addMessage('No POST data recieved');
			$this->_helper->redirector('index');
		}
		$user = $this->_getuser();
		
		if (null!==$this->getRequest()->getParam('delete')) {
			$user->delete();
			$this->getHelper('FlashMessenger')->addMessage('The user has been deleted.');
			$this->_helper->redirector('index');
		}
		
		$form = $user->getForm();
		if (!$form->isValid($this->getRequest()->getParams())) {
			return $this->_forward('edit');
		} else {
			$user
				->setFromArray($form->getValues())
				->setFromArray(array('tenant' => $this->_tenant->code));
			try {
				$user->save();
			} catch (Zend_Db_Statement_Exception $e) {
				$this->getHelper('FlashMessenger')->setNamespace('error')->addMessage($e->getMessage());
				return $this->_forward('edit');
			}
			$this->getHelper('FlashMessenger')->addMessage('Data saved');
			$this->_helper->redirector('index');
		}
	}
	
	/**
	 * @return OpenSKOS_Db_Table_Row_User
	 */
	protected function _getUser()
	{
		$model = new OpenSKOS_Db_Table_Users();
		if (null === ($id = $this->getRequest()->getParam('user'))) {
			//create a new collection:
			$user = $model->createRow(array('tenant' => $this->_tenant->code));
		} else {
			$user = $model->find((int)$id)->current();
			if (null === $user) {
				$this->getHelper('FlashMessenger')->setNamespace('error')->addMessage('User `'.$id.'` not found');
				$this->_helper->redirector('index');
			}
		}
		
		if ($user->tenant != $this->_tenant->code) {
			$this->getHelper('FlashMessenger')->setNamespace('error')->addMessage('You are not allowed to edit this user.');
			$this->_helper->redirector('index');
		}
		return $user;
	}
	
}