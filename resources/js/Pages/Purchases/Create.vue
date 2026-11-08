<script setup>
  import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
  import { getToday } from "@/common";
  import { Head } from "@inertiajs/vue3";
  import { onMounted, reactive, ref, computed } from "vue";
  import { router } from "@inertiajs/inertia";
  import ValidationErrors from "@/Components/ValidationErrors.vue";
  import MicroModal from "@/Components/MicroModal.vue";

  const props = defineProps({
    items: Array,
    errors: Object,
  });

  const form = reactive({
    date: null,
    customer_id: null,
    status: true,
    items: [],
  });

  // propsのままだと変更できないので新たに配列を作って追加
  const itemList = ref([]); //リアクティブな配列を準備する

  onMounted(() => {
    // ページ読み込み後、即座に実行
    form.date = getToday();

    props.items.forEach((item) => {
      // 配列を一つずつ処理
      itemList.value.push({
        // 配列に1つずつ追加
        id: item.id,
        name: item.name,
        price: item.price,
        // 数量の初期値は０
        quantity: 0,
      });
    });
  });

  // 合計金額の計算
  // computedで変更があり次第、再計算する
  // computedはreturnが必須
  const totalPrice = computed(() => {
    let total = 0;
    itemList.value.forEach((item) => {
      total += item.price * item.quantity;
    });

    return total;
  });

  const storePurchase = () => {
    itemList.value.forEach((item) => {
      if (item.quantity > 0) {
        form.items.push({
          id: item.id,
          quantity: item.quantity,
        });
      }
    });

    router.post(route("purchases.store"), form);
  };

  // option用
  const quantity = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];

  // 子から受け取った会員ID(emitで打ち上げられたID)をモーダルの`@update:customerId`経由で受け取り、customer_idとして保存
  const setCustomerId = (id) => {
    form.customer_id = id;
  };

</script>

<template>
  <Head title="購入画面" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">購入画面</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <ValidationErrors :errors="errors" />
            <section class="text-gray-600 body-font relative">
              <form action="" @submit.prevent="storePurchase">
                <div class="container px-5 py-8 mx-auto">
                  <div class="lg:w-1/2 md:w-2/3 mx-auto">
                    <div class="flex flex-wrap -m-2">
                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="date" class="leading-7 text-sm text-gray-600"
                            >日付</label
                          >
                          <input
                            type="date"
                            id="date"
                            name="date"
                            v-model="form.date"
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          />
                        </div>
                      </div>

                      <div class="p-2 w-full">
                        <div class="relative">
                          <label for="customer" class="leading-7 text-sm text-gray-600"
                            >会員名</label
                          >
                          <MicroModal
                           @update:customerId="setCustomerId"
                          />
                        </div>
                      </div>

                      <div class="w-full mt-8 mx-auto overflow-auto">
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
                                商品名
                              </th>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100"
                              >
                                金額
                              </th>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100"
                              >
                                数量
                              </th>
                              <th
                                class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100"
                              >
                                小計
                              </th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr :key="item.id" v-for="item in itemList">
                              <td class="px-4 py-3 border-b-2 border-gray-200">
                                {{ item.id }}
                              </td>
                              <td class="px-4 py-3 border-b-2 border-gray-200">
                                {{ item.name }}
                              </td>
                              <td class="px-4 py-3 border-b-2 border-gray-200">
                                {{ item.price }}
                              </td>
                              <td class="px-4 py-3 border-b-2 border-gray-200">
                                <select name="quantity" v-model="item.quantity">
                                  <option v-for="q in quantity" :value="q">
                                    {{ q }}
                                  </option>
                                </select>
                              </td>
                              <td class="px-4 py-3 border-b-2 border-gray-200">
                                {{ item.price * item.quantity }}
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <div class="p-2 w-full">
                        <div class="">
                          <label for="price" class="leading-7 text-sm text-gray-600"
                            >合計金額
                          </label>
                          <div
                            class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                          >
                            {{ totalPrice }} 円
                          </div>
                        </div>
                      </div>
                      <div class="p-2 w-full">
                        <button
                          class="flex mx-auto text-white bg-indigo-500 border-0 py-2 px-8 focus:outline-none hover:bg-indigo-600 rounded text-lg"
                        >
                          登録する
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </section>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
