@extends('web.layouts.app')

@section('title', 'Indian MSME Store — Multi Vendor Cart')

@section('content')

    <!-- ========== Breadcrumb Section Start ========== -->
    <div class="container py-12">
      <div class="breadcrumb">
        <ul>
          <li>
            <a href="{{ route('home') }}">
              <span class="inline-flex items-center justify-center">
                <i
                  class="hgi hgi-stroke hgi-home-01 text-2xl leading-6"
                ></i></span
              >Home</a>
          </li>
          <li class="text-light-disabled-text">&#8226;</li>
          <li><span class="text-sm leading-[22px]">Cart</span></li>
        </ul>
      </div>
    </div>

    <!-- ========== Breadcrumb Section End ========== -->

    <!-- ========== Cart vendor Section Start ========== -->
    <section class="pb-[70px]">
      <div class="container">
        <div class="grid grid-cols-12">
          <div class="xl:col-span-8 col-span-12">
            <div class="flex items-center justify-between mb-6 xl:px-2 px-0">
              <div class="flex items-center gap-x-1">
                <h5>Cart</h5>
                <p>(3 item)</p>
              </div>
              <div class="flex items-center">
                <button
                  class="inline-flex gap-x-1 items-center justify-center font-semibold leading-[26px] text-error"
                >
                  <i
                    class="hgi hgi-stroke hgi-cancel-01 text-xl leading-5 font-semibold"
                  ></i>
                  Remove All
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="grid grid-cols-12 gap-x-6 gap-y-6">
          <div class="xl:col-span-8 col-span-12">
            <div
              class="wishlist-table-wrapper border-gray-300 rounded-2xl border overflow-x-auto"
            >
              <table class="w-full wishlist-table">
                <thead class="bg-gray-200">
                  <tr>
                    <th class="product text-left font-semibold pl-4">
                      <p class="product-name">Product</p>
                    </th>
                    <th class="product-price text-left py-2.5 font-semibold">
                      Price
                    </th>
                    <th class="product-quantity text-left py-2.5 font-semibold">
                      Quantity
                    </th>
                    <th
                      class="product-total-price text-left py-2.5 font-semibold"
                    >
                      Total Price
                    </th>
                    <th
                      class="product-actions text-center py-2.5 font-semibold pr-4"
                    >
                      Remove
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td
                      data-title="Brand Name "
                      class="py-3 px-3 lg:px-4"
                      colspan="5"
                    >
                      <div
                        class="flex items-center gap-x-4 flex-col md:flex-row gap-y-4"
                      >
                        <div
                          class="hidden has-[input:checked]:hover:bg-primary/8 lg:flex items-center justify-center w-10 h-10 bg-transparent rounded-full hover:bg-[rgba(145,158,171,0.08)] transition-colors duration-300 ease-in-out"
                        >
                          <label
                            class="relative inline-flex w-5 h-5 cursor-pointer items-center justify-center"
                          >
                            <!-- Checkbox Input -->
                            <input
                              type="checkbox"
                              class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all"
                            />

                            <!-- Checkbox Icon -->
                            <span
                              class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                            >
                              <i
                                class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                              ></i>
                            </span>
                          </label>
                        </div>

                        <a class='inline-flex items-center justify-center gap-x-2 text-light-primary-text font-semibold group hover:text-primary transition-colors duration-300 ease-in-out' href='#'>
                          <span class="inline-flex items-center justify-center">
                            <i class="hgi hgi-stroke hgi-store-01 leading-6 text-2xl group-hover:text-primary"></i>
                          </span>
                          Fresh Organic Farm
                          <span class="inline-flex items-center justify-center">
                            <i class="hgi hgi-stroke hgi-arrow-right-01 text-[22px] leading-[22px] group-hover:text-primary"></i>
                          </span>
                        </a>
                      </div>
                    </td>
                  </tr>
                  <tr class="py-4">
                    <td data-title="Product " class="py-4 px-3 lg:px-4 product">
                      <div
                        class="flex items-end md:items-start gap-x-4 flex-col md:flex-row gap-y-4"
                      >
                        <div
                          class="hidden has-[input:checked]:hover:bg-primary/8 lg:flex items-center justify-center w-10 h-10 bg-transparent rounded-full hover:bg-[rgba(145,158,171,0.08)] transition-colors duration-300 ease-in-out"
                        >
                          <label
                            class="relative inline-flex w-5 h-5 cursor-pointer items-center justify-center"
                          >
                            <!-- Checkbox Input -->
                            <input
                              type="checkbox"
                              class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all"
                            />

                            <!-- Checkbox Icon -->
                            <span
                              class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                            >
                              <i
                                class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                              ></i>
                            </span>
                          </label>
                        </div>

                        <div
                          class="product-thumbnail max-w-[120px] h-[120px] rounded-2xl bg-[#F4F3F5]"
                        >
                          <div class="w-full h-full flex items-center justify-center text-gray-400"><i class="hgi hgi-stroke hgi-package text-4xl"></i></div>
                        </div>
                        <div
                          class="flex flex-col gap-y-2 items-end md:items-start"
                        >
                          <a class='product-name text-light-primary-text font-semibold truncate hover:text-primary transition-colors duration-300' href='#'>
                            GreenLeaf Organic Spinach
                          </a>
                          <p
                            class="text-sm leading-[22px] font-normal text-light-secondary-text inline-flex items-center gap-x-2.5"
                          >
                            Color: Black, Size:250 ML
                            <a
                              href="#"
                              class="inline-flex items-center justify-center"
                              ><i
                                class="hgi hgi-stroke hgi-edit-02 text-base leading-4"
                              ></i
                            ></a>
                          </p>
                          <p
                            class="text-sm leading-[22px] font-normal text-light-secondary-text inline-flex items-center gap-x-2.5"
                          >
                            Available: 2
                          </p>
                          <div class="rating-section flex items-center">
                            <div
                              class="text-amber-400 w-[90px] h-4.5 bg-repeat-x overflow-hidden bg-position-[0_0]"
                            >
                              <div
                                style="width: 80%"
                                class="text-amber-400 h-4.5 bg-repeat-x bg-position-[0_-18px]"
                              ></div>
                            </div>
                            <span
                              class="text-sm leading-[22px] font-normal inline-block ml-1"
                              >(118)</span
                            >
                          </div>
                        </div>
                      </div>
                    </td>
                    <td
                      data-title="Price "
                      class="capitalize py-4 px-3 lg:px-0 product-price"
                    >
                      <div class="flex items-center gap-x-3">
                        <span class="text-light-primary-text font-semibold"
                          >₹270.00</span
                        >
                        <span
                          class="line-through text-light-disabled-text font-normal"
                          >₹299.00</span
                        >
                      </div>
                    </td>
                    <td
                      data-title="Quantity "
                      class="capitalize py-4 px-3 lg:px-0 product-quantity"
                    >
                      <div
                        class="border border-gray-300 inline-flex items-center justify-center rounded-[80px] max-w-[108px] py-2.5 px-4"
                      >
                        <button
                          class="inline-flex items-center justify-center hover:text-primary"
                        >
                          <i
                            class="hgi hgi-stroke hgi-remove-circle text-xl leading-6"
                          ></i>
                        </button>
                        <input
                          type="text"
                          readonly
                          value="1"
                          class="border-0 w-full grow text-center focus:outline-none font-semibold text-light-primary-text"
                        />

                        <button
                          class="inline-flex items-center justify-center hover:text-primary"
                        >
                          <i
                            class="hgi hgi-stroke hgi-add-circle text-xl leading-6"
                          ></i>
                        </button>
                      </div>
                    </td>
                    <td
                      data-title="Total Price "
                      class="capitalize py-4 px-3 lg:px-0 product-total-price"
                    >
                      <p class="font-semibold text-light-primary-text">
                        ₹270.00
                      </p>
                    </td>
                    <td
                      data-title="Action "
                      class="capitalize py-4 px-3 lg:px-4 product-actions"
                    >
                      <div
                        class="flex items-center justify-center gap-x-2 md:gap-x-6"
                      >
                        <button
                          class="inline-flex items-center justify-center product-add-to-favourite"
                        >
                          <i
                            class="hgi hgi-stroke hgi-favourite text-2xl leading-6"
                          ></i>
                        </button>
                        <button
                          class="inline-flex items-center justify-center product-remove"
                        >
                          <i
                            class="hgi hgi-stroke hgi-delete-01 text-2xl leading-6"
                          ></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr class="py-4">
                    <td data-title="Product " class="py-4 px-3 lg:px-4 product">
                      <div
                        class="flex items-end md:items-start gap-x-4 flex-col md:flex-row gap-y-4"
                      >
                        <div
                          class="hidden has-[input:checked]:hover:bg-primary/8 lg:flex items-center justify-center w-10 h-10 bg-transparent rounded-full hover:bg-[rgba(145,158,171,0.08)] transition-colors duration-300 ease-in-out"
                        >
                          <label
                            class="relative inline-flex w-5 h-5 cursor-pointer items-center justify-center"
                          >
                            <!-- Checkbox Input -->
                            <input
                              type="checkbox"
                              class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all"
                            />

                            <!-- Checkbox Icon -->
                            <span
                              class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                            >
                              <i
                                class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                              ></i>
                            </span>
                          </label>
                        </div>

                        <div
                          class="product-thumbnail max-w-[120px] h-[120px] rounded-2xl bg-[#F4F3F5]"
                        >
                          <div class="w-full h-full flex items-center justify-center text-gray-400"><i class="hgi hgi-stroke hgi-package text-4xl"></i></div>
                        </div>
                        <div
                          class="flex flex-col gap-y-2 items-end md:items-start"
                        >
                          <a class='product-name text-light-primary-text font-semibold truncate hover:text-primary transition-colors duration-300' href='#'>
                            GreenLeaf Organic Spinach
                          </a>
                          <p
                            class="text-sm leading-[22px] font-normal text-light-secondary-text inline-flex items-center gap-x-2.5"
                          >
                            Color: Black, Size:250 ML
                            <a
                              href="#"
                              class="inline-flex items-center justify-center"
                              ><i
                                class="hgi hgi-stroke hgi-edit-02 text-base leading-4"
                              ></i
                            ></a>
                          </p>
                          <p
                            class="text-sm leading-[22px] font-normal text-light-secondary-text inline-flex items-center gap-x-2.5"
                          >
                            Available: 2
                          </p>
                          <div class="rating-section flex items-center">
                            <div
                              class="text-amber-400 w-[90px] h-4.5 bg-repeat-x overflow-hidden bg-position-[0_0]"
                            >
                              <div
                                style="width: 80%"
                                class="text-amber-400 h-4.5 bg-repeat-x bg-position-[0_-18px]"
                              ></div>
                            </div>
                            <span
                              class="text-sm leading-[22px] font-normal inline-block ml-1"
                              >(118)</span
                            >
                          </div>
                        </div>
                      </div>
                    </td>
                    <td
                      data-title="Price "
                      class="capitalize py-4 px-3 lg:px-0 product-price"
                    >
                      <div class="flex items-center gap-x-3">
                        <span class="text-light-primary-text font-semibold"
                          >₹270.00</span
                        >
                        <span
                          class="line-through text-light-disabled-text font-normal"
                          >₹299.00</span
                        >
                      </div>
                    </td>
                    <td
                      data-title="Quantity "
                      class="capitalize py-4 px-3 lg:px-0 product-quantity"
                    >
                      <div
                        class="border border-gray-300 inline-flex items-center justify-center rounded-[80px] max-w-[108px] py-2.5 px-4"
                      >
                        <button
                          class="inline-flex items-center justify-center hover:text-primary"
                        >
                          <i
                            class="hgi hgi-stroke hgi-remove-circle text-xl leading-6"
                          ></i>
                        </button>
                        <input
                          type="text"
                          readonly
                          value="1"
                          class="border-0 w-full grow text-center focus:outline-none font-semibold text-light-primary-text"
                        />

                        <button
                          class="inline-flex items-center justify-center hover:text-primary"
                        >
                          <i
                            class="hgi hgi-stroke hgi-add-circle text-xl leading-6"
                          ></i>
                        </button>
                      </div>
                    </td>
                    <td
                      data-title="Total Price "
                      class="capitalize py-4 px-3 lg:px-0 product-total-price"
                    >
                      <p class="font-semibold text-light-primary-text">
                        ₹270.00
                      </p>
                    </td>
                    <td
                      data-title="Action "
                      class="capitalize py-4 px-3 lg:px-4 product-actions"
                    >
                      <div
                        class="flex items-center justify-center gap-x-2 md:gap-x-6"
                      >
                        <button
                          class="inline-flex items-center justify-center product-add-to-favourite"
                        >
                          <i
                            class="hgi hgi-stroke hgi-favourite text-2xl leading-6"
                          ></i>
                        </button>
                        <button
                          class="inline-flex items-center justify-center product-remove"
                        >
                          <i
                            class="hgi hgi-stroke hgi-delete-01 text-2xl leading-6"
                          ></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td
                      data-title="Brand Name "
                      class="py-3 px-3 lg:px-4"
                      colspan="5"
                    >
                      <div
                        class="flex items-center gap-x-4 flex-col md:flex-row gap-y-4"
                      >
                        <div
                          class="hidden has-[input:checked]:hover:bg-primary/8 lg:flex items-center justify-center w-10 h-10 bg-transparent rounded-full hover:bg-[rgba(145,158,171,0.08)] transition-colors duration-300 ease-in-out"
                        >
                          <label
                            class="relative inline-flex w-5 h-5 cursor-pointer items-center justify-center"
                          >
                            <!-- Checkbox Input -->
                            <input
                              type="checkbox"
                              class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all"
                            />

                            <!-- Checkbox Icon -->
                            <span
                              class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                            >
                              <i
                                class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                              ></i>
                            </span>
                          </label>
                        </div>

                        <a class='inline-flex items-center justify-center gap-x-2 text-light-primary-text font-semibold group hover:text-primary transition-colors duration-300 ease-in-out' href='#'>
                          <span class="inline-flex items-center justify-center">
                            <i class="hgi hgi-stroke hgi-store-01 leading-6 text-2xl group-hover:text-primary"></i>
                          </span>
                          Premium Wellness Group
                          <span class="inline-flex items-center justify-center">
                            <i class="hgi hgi-stroke hgi-arrow-right-01 text-[22px] leading-[22px] group-hover:text-primary"></i>
                          </span>
                        </a>
                      </div>
                    </td>
                  </tr>
                  <tr class="py-4">
                    <td data-title="Product " class="py-4 px-3 lg:px-4 product">
                      <div
                        class="flex items-end md:items-start gap-x-4 flex-col md:flex-row gap-y-4"
                      >
                        <div
                          class="hidden has-[input:checked]:hover:bg-primary/8 lg:flex items-center justify-center w-10 h-10 bg-transparent rounded-full hover:bg-[rgba(145,158,171,0.08)] transition-colors duration-300 ease-in-out"
                        >
                          <label
                            class="relative inline-flex w-5 h-5 cursor-pointer items-center justify-center"
                          >
                            <!-- Checkbox Input -->
                            <input
                              type="checkbox"
                              class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all"
                            />

                            <!-- Checkbox Icon -->
                            <span
                              class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                            >
                              <i
                                class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                              ></i>
                            </span>
                          </label>
                        </div>

                        <div
                          class="product-thumbnail max-w-[120px] h-[120px] rounded-2xl bg-[#F4F3F5]"
                        >
                          <div class="w-full h-full flex items-center justify-center text-gray-400"><i class="hgi hgi-stroke hgi-package text-4xl"></i></div>
                        </div>
                        <div
                          class="flex flex-col gap-y-2 items-end md:items-start"
                        >
                          <a class='product-name text-light-primary-text font-semibold truncate hover:text-primary transition-colors duration-300' href='#'>
                            GreenLeaf Organic Spinach
                          </a>
                          <p
                            class="text-sm leading-[22px] font-normal text-light-secondary-text inline-flex items-center gap-x-2.5"
                          >
                            Color: Black, Size:250 ML
                            <a
                              href="#"
                              class="inline-flex items-center justify-center"
                              ><i
                                class="hgi hgi-stroke hgi-edit-02 text-base leading-4"
                              ></i
                            ></a>
                          </p>
                          <p
                            class="text-sm leading-[22px] font-normal text-light-secondary-text inline-flex items-center gap-x-2.5"
                          >
                            Available: 2
                          </p>
                          <div class="rating-section flex items-center">
                            <div
                              class="text-amber-400 w-[90px] h-4.5 bg-repeat-x overflow-hidden bg-position-[0_0]"
                            >
                              <div
                                style="width: 80%"
                                class="text-amber-400 h-4.5 bg-repeat-x bg-position-[0_-18px]"
                              ></div>
                            </div>
                            <span
                              class="text-sm leading-[22px] font-normal inline-block ml-1"
                              >(118)</span
                            >
                          </div>
                        </div>
                      </div>
                    </td>
                    <td
                      data-title="Price "
                      class="capitalize py-4 px-3 lg:px-0 product-price"
                    >
                      <div class="flex items-center gap-x-3">
                        <span class="text-light-primary-text font-semibold"
                          >₹270.00</span
                        >
                        <span
                          class="line-through text-light-disabled-text font-normal"
                          >₹299.00</span
                        >
                      </div>
                    </td>
                    <td
                      data-title="Quantity "
                      class="capitalize py-4 px-3 lg:px-0 product-quantity"
                    >
                      <div
                        class="border border-gray-300 inline-flex items-center justify-center rounded-[80px] max-w-[108px] py-2.5 px-4"
                      >
                        <button
                          class="inline-flex items-center justify-center hover:text-primary"
                        >
                          <i
                            class="hgi hgi-stroke hgi-remove-circle text-xl leading-6"
                          ></i>
                        </button>
                        <input
                          type="text"
                          readonly
                          value="1"
                          class="border-0 w-full grow text-center focus:outline-none font-semibold text-light-primary-text"
                        />

                        <button
                          class="inline-flex items-center justify-center hover:text-primary"
                        >
                          <i
                            class="hgi hgi-stroke hgi-add-circle text-xl leading-6"
                          ></i>
                        </button>
                      </div>
                    </td>
                    <td
                      data-title="Total Price "
                      class="capitalize py-4 px-3 lg:px-0 product-total-price"
                    >
                      <p class="font-semibold text-light-primary-text">
                        ₹270.00
                      </p>
                    </td>
                    <td
                      data-title="Action "
                      class="capitalize py-4 px-3 lg:px-4 product-actions"
                    >
                      <div
                        class="flex items-center justify-center gap-x-2 md:gap-x-6"
                      >
                        <button
                          class="inline-flex items-center justify-center product-add-to-favourite"
                        >
                          <i
                            class="hgi hgi-stroke hgi-favourite text-2xl leading-6"
                          ></i>
                        </button>
                        <button
                          class="inline-flex items-center justify-center product-remove"
                        >
                          <i
                            class="hgi hgi-stroke hgi-delete-01 text-2xl leading-6"
                          ></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="xl:col-span-4 col-span-12">
            <div
              class="border border-gray-300 rounded-2xl md:px-6 md:py-6 px-3 py-4 flex flex-col gap-y-6 bg-white"
            >
              <div>
                <a
                  href="#"
                  class="w-full px-4 py-4 inline-flex items-center gap-x-2 bg-[rgba(0,171,85,0.08)] text-light-primary-text text-sm leading-[22px] font-bold rounded-[100px]"
                >
                  <span
                    class="inline-flex items-center justify-center text-primary"
                    ><i class="hgi hgi-stroke hgi-car-03 text-2xl leading-6"></i
                  ></span>
                  Spend ₹600.00 for
                  <span class="text-base leading-6 font-semibold text-primary"
                    >Free Shipping</span
                  >
                </a>
              </div>
              <div class="border border-gray-300 md:p-6 p-3 rounded-2xl">
                <div class="flex flex-col gap-y-6">
                  <h5>Order Summary</h5>
                  <!-- Coupon code  -->
                  <div
                    class="input-group relative large w-full mx-auto ps-3.5 pe-24 py-3 rounded-[100px]"
                  >
                    <input
                      type="text"
                      class="form-control"
                      placeholder="Coupon Code"
                    />
                    <button
                      class="btn btn-primary btn-large px-[22px] py-[11px] rounded-l-none rounded-r-[100px] absolute top-0 right-0 h-full w-auto"
                    >
                      Apply
                    </button>
                  </div>
                  <!-- total -->
                  <div>
                    <div
                      class="flex flex-col gap-y-6 pb-4 border-b border-gray-300"
                    >
                      <p class="flex items-center justify-between">
                        Sub-Total<span class="text-gray-900">₹810.00</span>
                      </p>
                      <p class="flex items-center justify-between">
                        VAT (5%)<span class="text-gray-900">₹40.50</span>
                      </p>
                    </div>
                    <h6
                      class="flex items-center justify-between text-light-primary-text pt-4"
                    >
                      Total<span class="text-gray-900">₹850.50</span>
                    </h6>
                  </div>
                </div>
              </div>
              <!-- Checkbox -->

              <label class="flex items-center cursor-pointer">
                <!-- custom checkbox -->
                <span
                  class="has-[input:checked]:hover:bg-[#06172e]/8 flex items-center justify-center w-10 h-10 bg-transparent rounded-full hover:bg-[#919EAB]/8 transition-colors duration-300 ease-in-out"
                >
                  <span
                    class="relative inline-flex w-5 h-5 items-center justify-center"
                  >
                    <input
                      type="checkbox"
                      class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all"
                    />

                    <!-- checkbox tick icon -->
                    <span
                      class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                    >
                      <i
                        class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                      ></i>
                    </span>
                  </span>
                </span>

                <!-- label text -->
                <span>
                  I agree with the
                  <span class="text-secondary underline font-semibold"
                    >Terms</span
                  >
                  and
                  <span class="text-secondary underline font-semibold"
                    >Conditions</span
                  >
                </span>
              </label>
              <!-- Checkout Buttons -->
              <div class="flex flex-col gap-y-6">
                <a class='btn btn-primary py-3 w-full rounded-[80px]' href='#'>
                  Proceed to checkout
                </a>
                <a class='btn btn-default outline shadow-none w-full py-[11px] rounded-[80px]' href='{{ route('shop') }}'>
                  Continue Shopping
                  <span class="inline-flex items-center justify-center"
                    ><i
                      class="hgi hgi-stroke hgi-arrow-right-02 text-[22px] leading-[22px]"
                    ></i
                  ></span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ========== Cart vendor Section End ========== -->

@endsection
