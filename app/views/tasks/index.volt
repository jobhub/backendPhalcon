<div class="page-header">
    <h1>
        Задания
    </h1>
    <p> {{ link_to("tasks/new", "Создать задание") }}</p>
    <p> {{ link_to("tasks/mytasks/"~userId, "Мои задания") }}</p>
    <p>  {{ link_to("offers/myoffers/"~userId, "Мои предложения") }}</p>
    <p>  {{ link_to("tasks/doingtasks/"~userId, "Мои выполняемые задания") }}</p>
    <p>  {{ link_to("tasks/workingtasks/"~userId, "Мне выполняют задания") }}</p>
</div>







