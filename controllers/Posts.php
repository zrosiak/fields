<?php namespace Bm\Field\Controllers;

use Flash;
use ApplicationException;
use Cache;
use BackendMenu;
use Redirect;
use Bm\Field\Models\Post;
use Bm\Field\Models\Category;
use Backend\Classes\Controller;

class Posts extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['bm.field.access_other_posts', 'bm.field.access_posts'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bm.Field', 'articles', 'articles');
    }

    public function index()
    {
        $this->vars['postsTotal'] = Post::count();
        $this->vars['postsPublished'] = Post::published()->count();
        $this->vars['postsExpired'] = Post::expired()->count();
        $this->vars['postsDrafts'] = $this->vars['postsTotal'] - $this->vars['postsPublished'] - $this->vars['postsExpired'];

        $this->asExtension('ListController')->index();
    }

    public function create()
    {
        $this->bodyClass = 'compact-container';

        return $this->asExtension('FormController')->create();
    }

    public function update($recordId = null)
    {
        $this->bodyClass = 'compact-container';

        return $this->asExtension('FormController')->update($recordId);
    }

    public function listExtendQuery($query)
    {
        if (!$this->user->hasAnyAccess(['bm.field.access_other_posts'])) {
            $query->where('user_id', $this->user->id);
        }

        $query->with('template');
    }

    public function formExtendQuery($query)
    {
        if (!$this->user->hasAnyAccess(['bm.field.access_other_posts'])) {
            $query->where('user_id', $this->user->id);
        }
    }

    // okrętka na Backend\Widgets\Filter::getOptionsFromModel()
    public function listFilterExtendQuery($query, $scope)
    {
        if (in_array($scope->scopeName, ['category', 'template'])) {
            $query->getModel()->setKeyName('name');
        }
    }

    public function index_onDelete()
    {
        if (
            ($checkedIds = post('checked'))
            && is_array($checkedIds)
            && count($checkedIds)
        ) {
            foreach ($checkedIds as $postId) {
                if (
                    (!$post = Post::find($postId))
                    || !$post->canEdit($this->user)
                ) {
                    continue;
                }

                $post->delete();
            }

            Flash::success('Successfully deleted those posts.');
        }

        return $this->listRefresh();
    }

    /**
     * {@inheritdoc}
     */
    public function listInjectRowClass($record, $definition = null)
    {
        if (
            !$record->published
            || $record->created_at->getTimestamp() > time()
            || (
                empty($record->expire_at) === false
                && strtotime($record->expire_at) <= time()
            )
        ) {
            return 'safe disabled';
        }
    }

    /**
     * zmiana nadrzędnej kategorii
     * @param int $parmam id posta
     */
    public function onChangeTemplate($param = null)
    {
        if (
            isset($param)
            && empty(post('Post')['template_id']) === false
        ) {
            $post = Post::where('id', (int)$param)->first();
            $post->template_id = (int)post('Post')['template_id'];
            $post->save();

            return Redirect::to('backend/bm/field/posts/update/' . $param);
        }
    }

    /**
     * Podgląd artykułu
     */
    public function onPreview($param = null)
    {
        if (isset($param)) {
            Cache::put('preview' . $param, post('Post'), 5);
        }
    }

    /**
     * Przejście do artykułu
     */
    public function onGoto($param = null)
    {
        if (isset($param) && $post = Post::find($param)) {
            return $post->url;
        }
    }
}
