<script setup>
  import FlashMessage from "@/Components/FlashMessage.vue";
  import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
  import { Head } from "@inertiajs/vue3";
  import { Link } from "@inertiajs/vue3";
  import { reactive } from "vue";
  import Pagination from "@/Components/Pagination.vue";
  import { ref } from "vue";
  import axios from 'axios';
  import { router } from "@inertiajs/vue3";

  const props = defineProps({
    customers: Object,
    filters: Object,
  });

  console.log("filters", props.filters);

  const perPage =
    typeof props.filters.perPage == "undefined" ? ref(5) : props.filters.perPage;

  // コントローラから渡ってきた`filters`をpropsとして渡して検索フォームに値を保持させる。
  const searchId = ref(props.filters.searchId);
  const searchName = ref(props.filters.searchName);
  const searchKana = ref(props.filters.searchKana);
  const searchTel = ref(props.filters.searchTel);
  const searchEmail = ref(props.filters.searchEmail);

  // ref の値を取得するには、.valueが必要
  const form = reactive({
    searchId: searchId.value,
    searchName: searchName.value,
    searchKana: searchKana.value,
    searchTel: searchTel.value,
    searchEmail: searchEmail.value,
  });

  const searchCustomers = () => {
    router.get(route("customers.index"), {
      searchId: searchId.value,
      searchName: searchName.value,
      searchKana: searchKana.value,
      searchTel: searchTel.value,
      searchEmail: searchEmail.value,
    });
  };

  const getTags = () => {
    router.get(
      "/customers",
      {
        perPage: perPage.value,
        searchId: searchId.value,
        searchName: searchName.value,
        searchKana: searchKana.value,
        searchTel: searchTel.value,
        searchEmail: searchEmail.value,
      },
      {
        preserveState: true,
        replace: true,
      }
    );
  };

  const clearSearchInput = () => {
    form.searchId = null;
    form.searchName = null;
    form.searchKana = null;
    form.searchTel = null;
    form.searchEmail = null;

    router.get(route("customers.index"), form);
  };


    const onClickAllExportBtn = async () => {
        try {
            const response = await axios.get(route("customers.exportAll"), {
                responseType: "blob",
            });

            const contentType = response.headers["content-type"];
            const blob = new Blob([response.data], { type: contentType });

            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'allCustomers.csv';
            link.click();
            window.URL.revokeObjectURL(url);

        } catch (error) {
            console.error('ファイルのダウンロードに失敗しました', error);
        }
    };
    const onClickSearchExportBtn = async () => {
        try {
            // 入力途中の値ではなく、表示中の一覧に適用された検索条件を送る
            const response = await axios.get(route("customers.exportSearch"), {
                params: {
                    searchId: props.filters.searchId,
                    searchName: props.filters.searchName,
                    searchKana: props.filters.searchKana,
                    searchTel: props.filters.searchTel,
                    searchEmail: props.filters.searchEmail,
                },
                responseType: "blob",
            });

            const url = window.URL.createObjectURL(response.data);
            const link = document.createElement("a");
            link.href = url;
            link.download = "searchedCustomers.csv";
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.setTimeout(() => window.URL.revokeObjectURL(url), 1000);
        } catch (error) {
            console.error("検索結果のダウンロードに失敗しました", error);
            window.alert("検索結果のダウンロードに失敗しました。検索条件を確認してください。");
        }
    };
</script>

<template>
  <Head title="顧客一覧" />

  <AuthenticatedLayout>
    <template>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">顧客一覧</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <section class="text-gray-600 body-font">
              <div class="container px-5 py-8 mx-auto">
                <FlashMessage />

                <div class="flex my-4 lg:w-4/5 w-full mx-auto">
                  <button
                    type="button"
                    @click="onClickAllExportBtn"
                    class="block w-fit w-2/5 ml-auto text-white whitespace-nowrap bg-amber-300 border-0 py-2 px-6 focus:outline-none hover:bg-amber-400 rounded"
                  >
                    全件エクスポート
                  </button>
                </div>
                <div class="flex my-4 lg:w-4/5 w-full mx-auto">
                  <button
                    type="button"
                    @click="onClickSearchExportBtn"
                    class="block w-fit w-2/5 ml-auto text-white whitespace-nowrap bg-amber-300 border-0 py-2 px-6 focus:outline-none hover:bg-amber-400 rounded"
                  >
                    検索結果をエクスポート
                  </button>
                </div>
                <div class="flex my-4 lg:w-4/5 w-full mx-auto">
                  <Link
                    as="button"
                    :href="route('customers.create')"
                    class="block lg:w-2/12 w-2/5 ml-auto text-white bg-indigo-500 border-0 py-2 px-6 focus:outline-none hover:bg-indigo-600 rounded"
                  >
                    顧客登録
                  </Link>
                </div>
                <div>
                  <h2 class="my-4 lg:w-4/5 w-full mx-auto">顧客検索</h2>
                  <div class="flex my-4 lg:w-4/5 w-full mx-auto">
                    <form class="w-full" @submit.prevent="searchCustomers">
                      <div class="lg:grid lg:grid-cols-3 lg:gap-3">
                        <div>
                          <label class="">ID: </label><br />
                          <input
                            type="number"
                            name="searchId"
                            :value="searchId"
                            @input="searchId = $event.target.value"
                            placeholder="ID"
                            class="mb-5 rounded text-gray-900 placeholder-gray-400"
                          />
                        </div>

                        <div>
                          <label class="">氏名: </label><br />
                          <input
                            type="text"
                            name="searchName"
                            :value="searchName"
                            @input="searchName = $event.target.value"
                            placeholder="氏名"
                            class="mb-5 rounded text-gray-900 placeholder-gray-400"
                          />
                        </div>
                        <div>
                          <label class="">カナ: </label><br />
                          <input
                            type="text"
                            name="searchKana"
                            :value="searchKana"
                            @input="searchKana = $event.target.value"
                            placeholder="カナ"
                            class="mb-5 rounded text-gray-900 placeholder-gray-400"
                          />
                        </div>

                        <div>
                          <label class="">電話番号:</label><br />
                          <input
                            type="tel"
                            name="searchTel"
                            :value="searchTel"
                            @input="searchTel = $event.target.value"
                            placeholder="電話番号"
                            class="mb-5 rounded text-gray-900 placeholder-gray-400"
                          />
                        </div>

                        <div>
                          <label class="">Email:</label><br />
                          <input
                            type="email"
                            name="searchEmail"
                            :value="searchEmail"
                            @input="searchEmail = $event.target.value"
                            placeholder="メールアドレス"
                            class="w-full mb-5 rounded text-gray-900 placeholder-gray-400"
                          />
                        </div>
                      </div>

                      <button
                        type="submit"
                        class="block lg:w-2/12 w-2/5 mb-3 ml-auto text-white  bg-teal-500 border-0 py-2 px-6 focus:outline-none hover:bg-teal-600 rounded"
                      >
                        検索
                      </button>
                      <button
                        type="button"
                        class="block lg:w-2/12 w-2/5 ml-auto text-white bg-gray-400 border-0 py-2 px-6 focus:outline-none hover:bg-gray-600 rounded"
                        @click="clearSearchInput"
                      >
                        クリア
                      </button>
                    </form>
                  </div>
                </div>

                <div class="lg:w-4/5 w-full mx-auto overflow-auto"></div>

                <div class="lg:w-4/5 w-full mx-auto overflow-auto">
                  <!-- result section -->
                  <div v-if="!Object.keys(filters).length">
                    <p>検索結果: {{ customers.total }} 件</p>
                  </div>
                  <div v-if="Object.keys(filters).length">
                    <p>検索結果: {{ customers.total }} 件</p>
                  </div>
                  <div class="flex">
                    <select
                      name="perPage"
                      id=""
                      v-model="perPage"
                      @change="getTags"
                      class="px-4 py-4 mb-4 lg:w-1/6 rounded-md bg-gray-50 focus:bg-white focus:ring-0 text-sm"
                    >
                      <option value="5">5 件表示</option>
                      <option value="10">10 件表示</option>
                      <option value="20">20 件表示</option>
                    </select>
                  </div>
                  <!-- table section -->
                  <table class="table-auto w-full text-left whitespace-no-wrap">
                    <thead>
                      <tr>
                        <th
                          class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl"
                        >
                          ID
                        </th>
                        <th
                          class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100"
                        >
                          氏名
                        </th>
                        <th
                          class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100"
                        >
                          カナ
                        </th>
                        <th
                          class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100"
                        >
                          電話番号
                        </th>
                        <th
                          class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100"
                        >
                          メールアドレス
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr :key="customer.id" v-for="customer in customers.data">
                        <td class="px-4 py-3 border-b-2 border-gray-200">
                          <Link
                            class="text-blue-400"
                            :href="
                              route('customers.show', {
                                customer: customer.id,
                              })
                            "
                          >
                            {{ customer.id }}
                          </Link>
                        </td>
                        <td class="px-4 py-3 border-b-2 border-gray-200">
                          {{ customer.name }}
                        </td>
                        <td class="px-4 py-3 border-b-2 border-gray-200">
                          {{ customer.kana }}
                        </td>
                        <td class="px-4 py-3 border-b-2 border-gray-200">
                          {{ customer.tel }}
                        </td>
                        <td class="px-4 py-3 border-b-2 border-gray-200">
                          {{ customer.email }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <Pagination
                class="mt-6 flex justify-center items-center"
                :links="customers.links"
              ></Pagination>
            </section>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
