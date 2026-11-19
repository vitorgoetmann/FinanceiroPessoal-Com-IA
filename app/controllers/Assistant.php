<?php

class Assistant extends Controller {
    private $accountModel;
    private $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->accountModel = new Account($this->db);
        $this->categoryModel = new Category($this->db);
    }

    public function index() {
        $userId = $this->getUserId();
        $accounts = $this->accountModel->getUserAccounts($userId);
        $categories = $this->categoryModel->getUserCategories($userId);

        $this->view('assistant/index', [
            'accounts' => $accounts,
            'categories' => $categories
        ]);
    }
}
