<?php
/**
 * @var \App\View\AppView $this
 */

?>

<div class="container mx-auto">
    <h1 class="text-3xl font-bold">
        Поиск товаров
    </h1>
</div>
<div class="container mx-auto mt-8">
    <form id="searchForm" class="flex">
        <div class="relative min-w-[200px] h-10">
            <input
                class="peer w-full h-full bg-transparent text-blue-gray-700 font-sans font-normal outline outline-0 focus:outline-0 disabled:bg-blue-gray-50 disabled:border-0 transition-all placeholder-shown:border placeholder-shown:border-blue-gray-200 placeholder-shown:border-t-blue-gray-200 border focus:border-2 focus:border-t-transparent text-sm px-3 py-2.5 rounded-[7px] border-blue-gray-200 focus:border-gray-900"
                placeholder=" "
                name="query"
            />
            <label
                class="flex w-full h-full select-none pointer-events-none absolute left-0 font-normal !overflow-visible truncate peer-placeholder-shown:text-blue-gray-500 leading-tight peer-focus:leading-tight peer-disabled:text-transparent peer-disabled:peer-placeholder-shown:text-blue-gray-500 transition-all -top-1.5 peer-placeholder-shown:text-sm text-[11px] peer-focus:text-[11px] before:content[' '] before:block before:box-border before:w-2.5 before:h-1.5 before:mt-[6.5px] before:mr-1 peer-placeholder-shown:before:border-transparent before:rounded-tl-md before:border-t peer-focus:before:border-t-2 before:border-l peer-focus:before:border-l-2 before:pointer-events-none before:transition-all peer-disabled:before:border-transparent after:content[' '] after:block after:flex-grow after:box-border after:w-2.5 after:h-1.5 after:mt-[6.5px] after:ml-1 peer-placeholder-shown:after:border-transparent after:rounded-tr-md after:border-t peer-focus:after:border-t-2 after:border-r peer-focus:after:border-r-2 after:pointer-events-none after:transition-all peer-disabled:after:border-transparent peer-placeholder-shown:leading-[3.75] text-gray-500 peer-focus:text-gray-900 before:border-blue-gray-200 peer-focus:before:!border-gray-900 after:border-blue-gray-200 peer-focus:after:!border-gray-900"
            >
                Фраза поиска
            </label>
        </div>
        <div class="relative min-w-[75px] h-10 ml-4">
            <input
                type="number"
                min="1"
                class="peer w-full h-full bg-transparent text-blue-gray-700 font-sans font-normal outline outline-0 focus:outline-0 disabled:bg-blue-gray-50 disabled:border-0 transition-all placeholder-shown:border placeholder-shown:border-blue-gray-200 placeholder-shown:border-t-blue-gray-200 border focus:border-2 focus:border-t-transparent text-sm px-3 py-2.5 rounded-[7px] border-blue-gray-200 focus:border-gray-900"
                placeholder=" "
                name="page"
            />
            <label
                class="flex w-full h-full select-none pointer-events-none absolute left-0 font-normal !overflow-visible truncate peer-placeholder-shown:text-blue-gray-500 leading-tight peer-focus:leading-tight peer-disabled:text-transparent peer-disabled:peer-placeholder-shown:text-blue-gray-500 transition-all -top-1.5 peer-placeholder-shown:text-sm text-[11px] peer-focus:text-[11px] before:content[' '] before:block before:box-border before:w-2.5 before:h-1.5 before:mt-[6.5px] before:mr-1 peer-placeholder-shown:before:border-transparent before:rounded-tl-md before:border-t peer-focus:before:border-t-2 before:border-l peer-focus:before:border-l-2 before:pointer-events-none before:transition-all peer-disabled:before:border-transparent after:content[' '] after:block after:flex-grow after:box-border after:w-2.5 after:h-1.5 after:mt-[6.5px] after:ml-1 peer-placeholder-shown:after:border-transparent after:rounded-tr-md after:border-t peer-focus:after:border-t-2 after:border-r peer-focus:after:border-r-2 after:pointer-events-none after:transition-all peer-disabled:after:border-transparent peer-placeholder-shown:leading-[3.75] text-gray-500 peer-focus:text-gray-900 before:border-blue-gray-200 peer-focus:before:!border-gray-900 after:border-blue-gray-200 peer-focus:after:!border-gray-900"
            >
                Страница
            </label>
        </div>
        <button type="submit" class="ml-4 p-1 rounded-[7px] border">Поиск</button>
    </form>
</div>
<div class="container mx-auto">
    <hr class="h-px my-8 bg-gray-200 border-0">
</div>
<div class="container mx-auto mt-4">
    <table id="productTable" class="table-auto border">
        <thead>
        <tr>
            <th class="font-bold p-2 border-b text-left">Позиция</th>
            <th class="font-bold p-2 border-b text-left">Бренд</th>
            <th class="font-bold p-2 border-b text-left">Продукт</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        <template id="rowTemplate">
            <tr class="hover:bg-blue-100 hover:bg-opacity-50">
                <td class="p-2 border-b text-left"></td>
                <td class="p-2 border-b text-left"></td>
                <td class="p-2 border-b text-left"></td>
            </tr>
        </template>
    </table>
</div>


<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", () => {
        let searchForm = document.getElementById('searchForm'),
            productTableTbody = document.getElementById('productTable').querySelector('tbody'),
            rowTemplate = document.getElementById('rowTemplate');

        searchForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            let result = await fetch('/products/find', {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.head.querySelector("[name=csrf-token]").content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    query: searchForm.query.value,
                    page: searchForm.page.value || 1,
                })
            });
            if (!result.ok) {
                alert('Ошибка');
                return;
            }
            let products = (await result.json())?.products;
            productTableTbody.innerHTML = '';
            products.forEach((product) => {
                let clone = rowTemplate.content.cloneNode(true);
                let td = clone.querySelectorAll("td");
                td[0].textContent = product.position;
                td[1].textContent = product.brand_name;
                td[2].textContent = product.name;

                productTableTbody.appendChild(clone);
            });
        });
    });
</script>
