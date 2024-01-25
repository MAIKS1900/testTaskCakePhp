<?php
declare(strict_types=1);

namespace App\Controller;

use App\Lib\Finder\WbProductsFinder;
use Cake\Validation\Validator;
use Cake\View\JsonView;

/**
 * Products Controller
 */
class ProductsController extends AppController
{
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    public function find(WbProductsFinder $finder)
    {
        $validator = new Validator();
        $errors = $validator->requirePresence('query')
            ->integer('page')
            ->add('page', 'page', [
                'rule' => function ($value) {
                    return (int)$value >= 1;
                },
                'message' => 'Page must be greater or equal then 1'
            ])
            ->validate($this->request->getData());
        if (!empty($errors)) {
            $this->response = $this->getResponse()->withStatus(400);
            $this->set(compact('errors'));
            $this->viewBuilder()->setOption('serialize', ['errors']);
            return null;
        }

        $query = $this->request->getData('query');
        $page = (int)$this->request->getData('page', 1);

        $products = $finder->setQuery($query)
            ->getPage($page);

        $this->set(compact('products'));
        $this->viewBuilder()->setOption('serialize', ['products']);
    }
}
