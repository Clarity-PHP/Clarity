<?php
declare(strict_types=1);

namespace framework\clarity\Http;

use framework\clarity\Http\enum\ResourceActionTypesEnum;
use framework\clarity\Http\interfaces\FormRequestFactoryInterface;
use framework\clarity\Http\interfaces\ResourceDataFilterInterface;
use framework\clarity\Http\interfaces\ResourceWriterInterface;
use framework\clarity\Http\interfaces\ServerRequestInterface;
use framework\clarity\Http\router\exceptions\HttpBadRequestException;
use framework\clarity\Http\router\exceptions\HttpForbiddenException;

abstract class AbstractResourceController
{
    protected array $forms = [
        ResourceActionTypesEnum::CREATE->value => FormRequest::class,
        ResourceActionTypesEnum::UPDATE->value => FormRequest::class,
        ResourceActionTypesEnum::PATCH->value => FormRequest::class,
    ];

    /**
     * @param ResourceDataFilterInterface $resourceDataFilter
     * @param ServerRequestInterface $request
     * @param FormRequestFactoryInterface $formRequestFactory
     * @param ResourceWriterInterface $resourceWriter
     */
    public function __construct(
        protected ResourceDataFilterInterface $resourceDataFilter,
        protected ServerRequestInterface $request,
        protected FormRequestFactoryInterface $formRequestFactory,
        protected ResourceWriterInterface $resourceWriter,
    ) {
        $this->resourceDataFilter
            ->setResourceName($this->getResourceName())
            ->setAccessibleFields($this->getAccessibleFilters())
            ->setAccessibleFilters($this->getAccessibleFields());

        $this->resourceWriter
            ->setResourceName($this->getResourceName());
    }

    /**
     * @return array
     */
    protected function getAvailableActions(): array
    {
        return [
            ResourceActionTypesEnum::INDEX,
            ResourceActionTypesEnum::VIEW,
            ResourceActionTypesEnum::CREATE,
            ResourceActionTypesEnum::UPDATE,
            ResourceActionTypesEnum::PATCH,
            ResourceActionTypesEnum::DELETE,
        ];
    }

    /**
     * @return string
     */
    abstract protected function getResourceName(): string;

    /**
     * Возврат имен свойств ресурса, доступных к чтению
     * Пример запроса:
     * ?fields=id,order_id,name
     * @return array
     */
    abstract protected function getAccessibleFields(): array;

    /**
     * Возврат имен свойств ресурса, доступных к фильтрации
     * Пример запроса:
     * ?filter[order_id][$eq]=3
     * @return array
     */
    abstract protected function getAccessibleFilters(): array;

    /**
     * @param ResourceActionTypesEnum $actionType
     * @return void
     * @throws HttpForbiddenException
     */
    protected function checkCallAvailability(ResourceActionTypesEnum $actionType): void
    {
        if (in_array($actionType, $this->getAvailableActions(), true) === false) {
            throw new HttpForbiddenException(
                sprintf("Action '%s' is not available for this resource", $actionType->name)
            );
        }
    }

    /**
     * Возврат ресурсов, по ограничениям указанным в строке запроса
     *
     * Пример запроса:
     * ?fields[]=id&fields[]=order_id&fields[]=name&filter[order_id][$eq]=3
     * Пример ответа:
     * application/json
     * [
     *     {
     *         "id": 1,
     *         "order_id":3,
     *         "name": "Некоторое имя 1"
     *     },
     *     {
     *         "id": 2,
     *         "order_id":3,
     *         "name": "Некоторое имя 2"
     *     },
     *     ...
     * ]
     * @return JsonResponse
     */
    public function actionList(): JsonResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::INDEX);

        $data = $this->resourceDataFilter->filterAll($this->request->getQueryParams());

        return new JsonResponse($data);
    }

    /**
     * Возврат ресурса, по ограничениям указанным в строке запроса
     *
     * Пример запроса:
     * ?fields[]=id&fields[]=name&filter[id][$eq]=1
     * Пример ответа:
     * application/json
     * {
     *     "id": 1,
     *     "name": "Некоторое имя 1"
     * },
     * @return JsonResponse
     */
    public function actionView(): JsonResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::VIEW);

        $data = $this->resourceDataFilter->filterOne($this->request->getQueryParams());

        return new JsonResponse($data);
    }

    /**
     * @throws HttpBadRequestException
     */
    public function actionCreate(): CreateResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::CREATE);

        $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::CREATE->value]);

        $form->setValues($this->request->getParsedBody());

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new HttpBadRequestException(implode(', ', $form->getErrors()));
        }

        $this->resourceWriter->create($form->getValues());

        return new CreateResponse();
    }

    /**
     * @param string|int|null $id
     * @return UpdateResponse
     * @throws HttpBadRequestException
     * @throws HttpForbiddenException
     */
    public function actionUpdate(string|int|null $id): UpdateResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::UPDATE);

        $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::UPDATE->value]);

        $form->setValues($this->request->getParsedBody());

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new HttpBadRequestException(implode(', ', $form->getErrors()));
        }

        $this->resourceWriter->update($id, $form->getValues());

        return new UpdateResponse();
    }

    /**
     * @throws HttpBadRequestException
     */
    public function actionPatch(string|int $id): PatchResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::PATCH);

        $form = $this->formRequestFactory->create($this->forms[ResourceActionTypesEnum::PATCH->value]);

        $form->setSkipEmptyValues();

        $form->validate();

        if (empty($form->getErrors()) === false) {
            throw new HttpBadRequestException(implode(', ', $form->getErrors()));
        }

        $this->resourceWriter->patch($id, $form->getValues());

        return new PatchResponse();
    }

    /**
     * @param string|int $id
     * @return DeleteResponse
     */
    public function actionDelete(string|int $id): DeleteResponse
    {
        $this->checkCallAvailability(ResourceActionTypesEnum::DELETE);

        $this->resourceWriter->delete($id);

        return new DeleteResponse();
    }
}
