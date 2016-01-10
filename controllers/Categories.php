<?php namespace Bm\Field\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Bm\Field\Models\Category;
use Bm\Field\Models\Post;

class Categories extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['bm.field.access_categories'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bm.Field', 'articles', 'categories');
    }

    public function index_onDelete()
    {
        $checkedIds = post('checked');
        $error = false;

        if (
            is_array($checkedIds)
            && count($checkedIds)
        ) {
            foreach ($checkedIds as $categoryId) {
                $error &= $this->deleteCategory($categoryId);
            }

            if ($error === false) {
                Flash::success('Kategorie usunięte pomyślnie');
            }
        }

        return $this->listRefresh();
    }

    /**
     * Ajax handler for deleting the record.
     * @param int $recordId The model primary key to delete.
     * @return mixed
     */
    public function update_onDelete($recordId = null)
    {
        if ($this->deleteCategory($recordId)) {
            Flash::success('Kategorie usunięte pomyślnie');

            if ($redirect = $this->makeRedirect('delete', (new Category()))) {
                return $redirect;
            }
        }
    }

    protected function deleteCategory($id = null)
    {
        $category = Category::find((int)$id);
        
        if ($category) {
            if (
                ($category->children && $category->children->count())
                || $category->getPostCountAttribute()
                //|| Post::whereIn('category_id', $category->getSubcategoriesId(true))->count() > 0
            ) {
                Flash::error('Nie można usunąć niepustej kategorii');

                return false;
            }

            $category->delete();
        }

        return true;
    }

    /**
     * zmiana nadrzędnej kategorii
     */
    public function onMove()
    {
        $result = false;
        $nodeId = post('node_id');
        $parentId = post('parent_id');
        $nodeStauts = post('status');
        $order = post('order', 0);

        if (
            isset($nodeId, $parentId)
            && $nodeId !== $parentId
        ) {
            $item = Category::find($nodeId);

            if ($item && $item->parent_id !== (int)$parentId) {
                $item->parent_id = (int)$parentId ?: null;
                $item->order = $order;
                $item->save();

                $result = count($item->getParents());
            }
        }

        return ['result' => $result];
    }
}
