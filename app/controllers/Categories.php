<?php

class Categories extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->categoryModel = new Category($this->db);
    }

    public function index() {
        $userId = $this->getUserId();
        $categories = $this->categoryModel->findAll(['user_id' => $userId], 'type ASC, name ASC');
        
        $this->view('categories/index', ['categories' => $categories]);
    }

    public function new() {
        $this->view('categories/form', ['category' => null]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('categories');
            return;
        }

        $userId = $this->getUserId();
        
        $data = [
            'user_id' => $userId,
            'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
            'type' => $_POST['type'],
            'color' => $_POST['color'] ?? '#00897B',
            'icon' => $_POST['icon'] ?? 'fas fa-tag',
            'is_default' => 0
        ];

        if ($this->categoryModel->create($data)) {
            $this->setFlash('success', 'Categoria criada com sucesso!');
        } else {
            $this->setFlash('error', 'Erro ao criar categoria');
        }

        $this->redirect('categories');
    }

    public function edit($id) {
        $userId = $this->getUserId();
        $category = $this->categoryModel->findById($id);

        if (!$category || $category['user_id'] != $userId) {
            $this->setFlash('error', 'Categoria não encontrada');
            $this->redirect('categories');
            return;
        }

        $this->view('categories/form', ['category' => $category]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('categories');
            return;
        }

        $userId = $this->getUserId();
        $category = $this->categoryModel->findById($id);

        if (!$category || $category['user_id'] != $userId) {
            $this->setFlash('error', 'Categoria não encontrada');
            $this->redirect('categories');
            return;
        }

        $data = [
            'name' => filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING),
            'type' => $_POST['type'],
            'color' => $_POST['color'] ?? '#00897B',
            'icon' => $_POST['icon'] ?? 'fas fa-tag'
        ];

        if ($this->categoryModel->update($id, $data)) {
            $this->setFlash('success', 'Categoria atualizada com sucesso!');
        } else {
            $this->setFlash('error', 'Erro ao atualizar categoria');
        }

        $this->redirect('categories');
    }

    public function delete($id) {
        $userId = $this->getUserId();
        $category = $this->categoryModel->findById($id);

        if (!$category || $category['user_id'] != $userId) {
            $this->json(['success' => false, 'message' => 'Categoria não encontrada'], 404);
            return;
        }

        if ($this->categoryModel->delete($id)) {
            $this->setFlash('success', 'Categoria excluída com sucesso!');
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Erro ao excluir categoria'], 500);
        }
    }
}
