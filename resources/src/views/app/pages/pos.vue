<template>
  <div class="pos_page">
    <div class="container-fluid p-0 app-admin-wrap layout-sidebar-large clearfix" id="pos">
      <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
      <b-row v-if="!isLoading">
        <!-- Card Left Panel Details Sale-->
        <b-col md="5">
          <b-card no-body class="card-order">
            <div class="main-header">
              <div class="logo">
                <router-link to="/app/dashboard">
                  <img :src="'/images/'+currentUser.logo" alt width="60" height="60">
                </router-link>
              </div>
              <div class="mx-auto"></div>

              <div class="header-part-right">
                <!-- Full screen toggle -->
                <i
                  class="i-Full-Screen header-icon d-none d-sm-inline-block"
                  @click="handleFullScreen"
                ></i>
                <!-- Grid menu Dropdown -->

                <div class="dropdown">
                  <b-dropdown
                    id="dropdown"
                    text="Dropdown Button"
                    class="m-md-2"
                    toggle-class="text-decoration-none"
                    no-caret
                    right
                    variant="link"
                  >
                    <template slot="button-content">
                      <i
                        class="i-Globe text-muted header-icon"
                        role="button"
                        id="dropdownMenuButton"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                      ></i>
                    </template>
                    <vue-perfect-scrollbar
                      :settings="{ suppressScrollX: true, wheelPropagation: false }"
                      ref="myData"
                      class="dropdown-menu-left rtl-ps-none notification-dropdown ps scroll"
                    >
                      <div class="menu-icon-grid">
                        <a @click="SetLocal('en')">
                          <i title="en" class="flag-icon flag-icon-squared flag-icon-gb"></i> English
                        </a>
                        <a @click="SetLocal('fr')">
                          <i title="fr" class="flag-icon flag-icon-squared flag-icon-fr"></i>
                          <span class="title-lang">French</span>
                        </a>
                        <a @click="SetLocal('ar')">
                          <i title="sa" class="flag-icon flag-icon-squared flag-icon-sa"></i>
                          <span class="title-lang">Arabic</span>
                        </a>
                        <a @click="SetLocal('tur')">
                          <i title="sa" class="flag-icon flag-icon-squared flag-icon-tr"></i>
                          <span class="title-lang">Turkish</span>
                        </a>

                        <a @click="SetLocal('sm_ch')">
                          <i title="sa" class="flag-icon flag-icon-squared flag-icon-cn"></i>
                          <span class="title-lang">Simplified Chinese</span>
                        </a>

                        <a @click="SetLocal('thai')">
                          <i title="sa" class="flag-icon flag-icon-squared flag-icon-tw"></i>
                          <span class="title-lang">Thaï</span>
                        </a>

                        <a @click="SetLocal('hn')">
                          <i title="sa" class="flag-icon flag-icon-squared flag-icon-in"></i>
                          <span class="title-lang">Hindi</span>
                        </a>

                        <a @click="SetLocal('de')">
                          <i title="de" class="flag-icon flag-icon-squared flag-icon-de"></i>
                          <span class="title-lang">German</span>
                        </a>
                        <a @click="SetLocal('es')">
                          <i title="es" class="flag-icon flag-icon-squared flag-icon-es"></i>
                          <span class="title-lang">Spanish</span>
                        </a>
                        <a @click="SetLocal('it')">
                          <i title="it" class="flag-icon flag-icon-squared flag-icon-it"></i>
                          <span class="title-lang">Italien</span>
                        </a>
                        <a @click="SetLocal('Ind')">
                          <i title="sa" class="flag-icon flag-icon-squared flag-icon-id"></i>
                          <span class="title-lang">Indonesian</span>
                        </a>

                        <a @click="SetLocal('tr_ch')">
                          <i title="sa" class="flag-icon flag-icon-squared flag-icon-cn"></i>
                          <span class="title-lang">Traditional Chinese</span>
                        </a>

                        <a @click="SetLocal('ru')">
                          <i title="sa" class="flag-icon flag-icon-squared flag-icon-ru"></i>
                          <span class="title-lang">Russian</span>
                        </a>

                        <a @click="SetLocal('vn')">
                          <i title="sa" class="flag-icon flag-icon-squared flag-icon-vn"></i>
                          <span class="title-lang">Vietnamese</span>
                        </a>
                        <a @click="SetLocal('kr')">
                          <i title="sa" class="flag-icon flag-icon-squared flag-icon-kr"></i>
                          <span class="title-lang">Korean</span>
                        </a>
                      </div>
                    </vue-perfect-scrollbar>
                  </b-dropdown>
                </div>

                <!-- User avatar dropdown -->
                <div class="dropdown">
                  <b-dropdown
                    id="dropdown-1"
                    text="Dropdown Button"
                    class="m-md-2 user col align-self-end"
                    toggle-class="text-decoration-none"
                    no-caret
                    variant="link"
                    right
                  >
                    <template slot="button-content">
                      <img
                        :src="'/images/avatar/'+currentUser.avatar"
                        id="userDropdown"
                        alt
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                      >
                    </template>

                    <div class="dropdown-menu-left" aria-labelledby="userDropdown">
                      <div class="dropdown-header">
                        <i class="i-Lock-User mr-1"></i>
                        <span>{{currentUser.username}}</span>
                      </div>
                      <router-link to="/app/profile" class="dropdown-item">{{$t('profil')}}</router-link>
                      <router-link
                        v-if="currentUserPermissions && currentUserPermissions.includes('setting_system')"
                        to="/app/settings/System_settings"
                        class="dropdown-item"
                      >{{$t('Settings')}}</router-link>
                      <a class="dropdown-item" href="#" @click.prevent="logoutUser">{{$t('logout')}}</a>
                    </div>
                  </b-dropdown>
                </div>
              </div>
            </div>
            <validation-observer ref="create_pos">
              <b-form @submit.prevent="Submit_Pos">
                <b-card-body>
                  <b-row>
                    <!-- Customer -->
                    <b-col lg="12" md="12" sm="12">
                      <validation-provider name="Customer" :rules="{ required: true}">
                        <b-input-group slot-scope="{ valid, errors }" class="input-customer">
                          <v-select
                            :class="{'is-invalid': !!errors.length}"
                            :state="errors[0] ? false : (valid ? true : null)"
                            v-model="sale.client_id"
                            :reduce="label => label.value"
                            :placeholder="$t('Choose_Customer')"
                            class="w-100"
                            :options="clients.map(clients => ({label: clients.name, value: clients.id}))"
                          />
                          <b-input-group-append>
                            <b-button variant="primary" @click="New_Client()">
                              <span>
                                <i class="i-Add-User"></i>
                              </span>
                            </b-button>
                          </b-input-group-append>
                        </b-input-group>
                      </validation-provider>
                    </b-col>

                    <!-- warehouse -->
                    <b-col lg="12" md="12" sm="12">
                      <validation-provider name="warehouse" :rules="{ required: true}">
                        <b-form-group slot-scope="{ valid, errors }" class="mt-2">
                          <v-select
                            :class="{'is-invalid': !!errors.length}"
                            :state="errors[0] ? false : (valid ? true : null)"
                            :disabled="details.length > 0"
                            @input="Selected_Warehouse"
                            v-model="sale.warehouse_id"
                            :reduce="label => label.value"
                            :placeholder="$t('Choose_Warehouse')"
                            :options="warehouses.map(warehouses => ({label: warehouses.name, value: warehouses.id}))"
                          />
                        </b-form-group>
                      </validation-provider>
                    </b-col>

                    <!-- Details Product  -->
                    <b-col md="12" class="mt-2">
                      <div class="pos-detail">
                        <div class="table-responsive">
                          <table class="table table-striped">
                            <thead>
                              <tr>
                                <th scope="col">{{$t('ProductName')}}</th>
                                <th scope="col">{{$t('Price')}}</th>
                                <th scope="col" class="text-center">{{$t('Qty')}}</th>
                                <th scope="col" class="text-center">{{$t('SubTotal')}}</th>
                                <th scope="col" class="text-center">
                                  <i class="fa fa-trash"></i>
                                </th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-if="details.length <= 0">
                                <td colspan="5">{{$t('NodataAvailable')}}</td>
                              </tr>
                              <tr v-for="(detail, index) in details" :key="index">
                                <td>
                                  <span>{{detail.code}}</span>
                                  <br>
                                  <span class="badge badge-success">{{detail.name}}</span>
                                  <i @click="Modal_Update_Detail(detail)" class="i-Edit"></i>
                                </td>
                                <td>{{currentUser.currency}} {{formatNumber(detail.Total_price, 2)}}</td>
                                <td>
                                  <div class="quantity">
                                    <b-input-group>
                                      <b-input-group-prepend>
                                        <span
                                          class="btn btn-primary btn-sm"
                                          @click="decrement(detail ,detail.detail_id)"
                                        >-</span>
                                      </b-input-group-prepend>

                                      <input
                                        class="form-control"
                                        @keyup="Verified_Qty(detail,detail.detail_id)"
                                        v-model.number="detail.quantity"
                                      >

                                      <b-input-group-append>
                                        <span
                                          class="btn btn-primary btn-sm"
                                          @click="increment(detail.detail_id)"
                                        >+</span>
                                      </b-input-group-append>
                                    </b-input-group>
                                  </div>
                                </td>
                                 <td class="text-center">{{currentUser.currency}} {{detail.subtotal.toFixed(2)}}</td>
                                <td>
                                  <a
                                    @click="delete_Product_Detail(detail.detail_id)"
                                    title="Delete"
                                  >
                                    <i class="i-Close-Window text-25 text-danger"></i>
                                  </a>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </b-col>
                  </b-row>

                  <!-- Calcul Grand Total -->
                  <div class="footer_panel">
                    <b-row>
                      <b-col md="12">
                        <div class="grandtotal">
                          <span>{{$t("Total")}} : {{currentUser.currency}} {{GrandTotal.toFixed(2)}}</span>
                        </div>
                      </b-col>

                      <!-- Order Tax  -->
                      <b-col lg="4" md="4" sm="12">
                        <validation-provider
                          name="Order Tax"
                          :rules="{ regex: /^\d*\.?\d*$/}"
                          v-slot="validationContext"
                        >
                          <b-form-group :label="$t('Tax')" append="%">
                            <b-input-group append="%">
                              <b-form-input
                                :state="getValidationState(validationContext)"
                                aria-describedby="OrderTax-feedback"
                                label="Order Tax"
                                v-model.number="sale.tax_rate"
                                @keyup="keyup_OrderTax()"
                              ></b-form-input>
                            </b-input-group>
                            <b-form-invalid-feedback
                              id="OrderTax-feedback"
                            >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                          </b-form-group>
                        </validation-provider>
                      </b-col>

                      <!-- Discount -->
                      <b-col lg="4" md="4" sm="12">
                        <validation-provider
                          name="Discount"
                          :rules="{ regex: /^\d*\.?\d*$/}"
                          v-slot="validationContext"
                        >
                          <b-form-group :label="$t('Discount')" append="%">
                            <b-input-group :append="currentUser.currency">
                              <b-form-input
                                :state="getValidationState(validationContext)"
                                aria-describedby="Discount-feedback"
                                label="Discount"
                                v-model.number="sale.discount"
                                @keyup="keyup_Discount()"
                              ></b-form-input>
                            </b-input-group>
                            <b-form-invalid-feedback
                              id="Discount-feedback"
                            >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                          </b-form-group>
                        </validation-provider>
                      </b-col>

                      <!-- Shipping  -->
                      <b-col lg="4" md="4" sm="12">
                        <validation-provider
                          name="Shipping"
                          :rules="{ regex: /^\d*\.?\d*$/}"
                          v-slot="validationContext"
                        >
                          <b-form-group :label="$t('Shipping')">
                            <b-input-group :append="currentUser.currency">
                              <b-form-input
                                :state="getValidationState(validationContext)"
                                aria-describedby="Shipping-feedback"
                                label="Shipping"
                                v-model.number="sale.shipping"
                                @keyup="keyup_Shipping()"
                              ></b-form-input>
                            </b-input-group>

                            <b-form-invalid-feedback
                              id="Shipping-feedback"
                            >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                          </b-form-group>
                        </validation-provider>
                      </b-col>

                      <b-col md="6" sm="12">
                        <b-button
                          @click="Reset_Pos()"
                          variant="danger ripple btn-rounded btn-block mt-1"
                        >
                          <i class="i-Power-2"></i>
                          {{ $t("Reset") }}
                        </b-button>
                      </b-col>
                      <b-col md="6" sm="12">
                        <b-button type="submit" variant="primary ripple mt-1 btn-rounded btn-block">
                          <i class="i-Checkout"></i>
                          {{ $t("payNow") }}
                        </b-button>
                      </b-col>
                    </b-row>
                  </div>
                </b-card-body>
              </b-form>
            </validation-observer>

            <!-- Update Detail Product -->
            <validation-observer ref="Update_Detail">
              <b-modal hide-footer size="md" id="form_Update_Detail" :title="detail.name">
                <b-form @submit.prevent="submit_Update_Detail">
                  <b-row>
                    <!-- Unit Price -->
                    <b-col lg="12" md="12" sm="12">
                      <validation-provider
                        name="Product Price"
                        :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('ProductPrice')" id="Price-input">
                          <b-form-input
                            label="Product Price"
                            v-model="detail.Unit_price"
                            :state="getValidationState(validationContext)"
                            aria-describedby="Price-feedback"
                          ></b-form-input>
                          <b-form-invalid-feedback
                            id="Price-feedback"
                          >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>

                    <!-- Tax Method -->
                    <b-col lg="12" md="12" sm="12">
                      <validation-provider name="Tax Method" :rules="{ required: true}">
                        <b-form-group slot-scope="{ valid, errors }" :label="$t('TaxMethod')">
                          <v-select
                            :class="{'is-invalid': !!errors.length}"
                            :state="errors[0] ? false : (valid ? true : null)"
                            v-model="detail.tax_method"
                            :reduce="label => label.value"
                            :placeholder="$t('Choose_Method')"
                            :options="
                           [
                            {label: 'Exclusive', value: '1'},
                            {label: 'Inclusive', value: '2'}
                           ]"
                          ></v-select>
                          <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>

                    <!-- Tax -->
                    <b-col lg="12" md="12" sm="12">
                      <validation-provider
                        name="Tax"
                        :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Tax')">
                          <b-input-group append="%">
                            <b-form-input
                              label="Tax"
                              v-model="detail.tax_percent"
                              :state="getValidationState(validationContext)"
                              aria-describedby="Tax-feedback"
                            ></b-form-input>
                          </b-input-group>
                          <b-form-invalid-feedback
                            id="Tax-feedback"
                          >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>

                    <!-- Discount Method -->
                    <b-col lg="12" md="12" sm="12">
                      <validation-provider name="Discount Method" :rules="{ required: true}">
                        <b-form-group slot-scope="{ valid, errors }" :label="$t('Discount_Method')">
                          <v-select
                            v-model="detail.discount_Method"
                            :reduce="label => label.value"
                            :placeholder="$t('Choose_Method')"
                            :class="{'is-invalid': !!errors.length}"
                            :state="errors[0] ? false : (valid ? true : null)"
                            :options="
                              [
                                {label: 'Percent %', value: '1'},
                                {label: 'Fixed', value: '2'}
                              ]"
                          ></v-select>
                          <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>

                    <!-- Discount Rate -->
                    <b-col lg="12" md="12" sm="12">
                      <validation-provider
                        name="Discount Rate"
                        :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Discount')">
                          <b-form-input
                            label="Discount"
                            v-model="detail.discount"
                            :state="getValidationState(validationContext)"
                            aria-describedby="Discount-feedback"
                          ></b-form-input>
                          <b-form-invalid-feedback
                            id="Discount-feedback"
                          >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>

                    <!-- Unit Sale -->
                    <b-col lg="12" md="12" sm="12">
                      <validation-provider name="Unit Sale" :rules="{ required: true}">
                        <b-form-group slot-scope="{ valid, errors }" :label="$t('UnitSale')">
                          <v-select
                            :class="{'is-invalid': !!errors.length}"
                            :state="errors[0] ? false : (valid ? true : null)"
                            v-model="detail.sale_unit_id"
                            :placeholder="$t('Choose_Unit_Sale')"
                            :reduce="label => label.value"
                            :options="units.map(units => ({label: units.name, value: units.id}))"
                          />
                          <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>

                    <b-col md="12">
                      <b-form-group>
                        <b-button variant="primary" type="submit">{{$t('submit')}}</b-button>
                      </b-form-group>
                    </b-col>
                  </b-row>
                </b-form>
              </b-modal>
            </validation-observer>
          </b-card>
        </b-col>

        <!-- Card right Of Products -->
        <b-col md="7">
          <b-card class="list-grid">
            <b-row>
              <b-col md="6">
                <button v-b-toggle.sidebar-category class="btn btn-outline-info mt-1 btn-block">
                  <i class="i-Two-Windows"></i>
                  {{$t('ListofCategory')}}
                </button>
              </b-col>
              <b-col md="6">
                <button v-b-toggle.sidebar-brand class="btn btn-outline-info mt-1 btn-block">
                  <i class="i-Library"></i>
                  {{$t('ListofBrand')}}
                </button>
              </b-col>


               <!-- Product -->
                <b-col md="12" class="mt-2 mb-2">
                  <h6>{{$t('ProductName')}}</h6>
                 
                  <div id="autocomplete" class="autocomplete">
                    <input 
                     :placeholder="$t('Scan_Search_Product_by_Code_Name')"
                      @keyup="search()" 
                      @focus="handleFocus"
                      @blur="handleBlur"
                      v-model="search_input"  
                      class="autocomplete-input" />
                    <ul class="autocomplete-result-list" v-show="focused">
                      <li class="autocomplete-result" v-for="product_fil in product_filter" @mousedown="SearchProduct(product_fil)">{{getResultValue(product_fil)}}</li>
                    </ul>
                </div>
                </b-col>

              <div class="col-md-12 d-flex flex-row flex-wrap bd-highlight list-item mt-2">
                <div
                  @click="Check_Product_Exist(product , product.id)"
                  v-for="product in products"
                  class="card o-hidden bd-highlight m-1"
                >
                  <div class="list-thumb d-flex">
                    <img alt :src="'/images/products/'+product.image">
                  </div>
                  <div class="flex-grow-1 d-bock">
                    <div
                      class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center"
                    >
                      <div class="w-40 w-sm-100 item-title">{{product.name}}</div>
                      <p class="text-muted text-small w-15 w-sm-100 mb-2">{{product.code}}</p>

                      <span
                        class="badge badge-primary w-15 w-sm-100 mb-2"
                      >{{currentUser.currency}} {{formatNumber(product.Net_price , 2)}}</span>
                      <p
                        class="m-0 text-muted text-small w-15 w-sm-100 d-none d-lg-block item-badges"
                      >
                        <span
                          class="badge badge-info"
                        >{{formatNumber(product.qte_sale , 2)}} {{product.unitSale}}</span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </b-row>
            <b-row>
              <b-col md="12" class="mt-4">
                <b-pagination
                  @change="Product_onPageChanged"
                  :total-rows="product_totalRows"
                  :per-page="product_perPage"
                  v-model="product_currentPage"
                  class="my-0 gull-pagination align-items-center"
                  align="center"
                  first-text
                  last-text
                >
                  <p class="list-arrow m-0" slot="prev-text">
                    <i class="i-Arrow-Left text-40"></i>
                  </p>
                  <p class="list-arrow m-0" slot="next-text">
                    <i class="i-Arrow-Right text-40"></i>
                  </p>
                </b-pagination>
              </b-col>
            </b-row>
          </b-card>
        </b-col>

        <!-- Sidebar Brand -->
        <b-sidebar id="sidebar-brand" :title="$t('ListofBrand')" bg-variant="white" right shadow>
          <div class="px-3 py-2">
            <b-row>
              <div class="col-md-12 d-flex flex-row flex-wrap bd-highlight list-item mt-2">
                <div
                  @click="GetAllBrands()"
                  :class="{ 'brand-Active' : brand_id == ''}"
                  class="card o-hidden bd-highlight m-1"
                >
                  <div class="list-thumb d-flex">
                    <img alt :src="'/images/no-image.png'">
                  </div>
                  <div class="flex-grow-1 d-bock">
                    <div
                      class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center"
                    >
                      <div class="item-title">{{$t('All_Brand')}}</div>
                    </div>
                  </div>
                </div>
                <div
                  class="card o-hidden bd-highlight m-1"
                  v-for="brand in paginated_Brands"
                  :key="brand.id"
                  @click="Products_by_Brands(brand.id)"
                  :class="{ 'brand-Active' : brand.id === brand_id}"
                >
                  <img alt :src="'/images/brands/'+brand.image">
                  <div class="flex-grow-1 d-bock">
                    <div
                      class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center"
                    >
                      <div class="item-title">{{brand.name}}</div>
                    </div>
                  </div>
                </div>
              </div>
            </b-row>

            <b-row>
              <b-col md="12" class="mt-4">
                <b-pagination
                  @change="BrandonPageChanged"
                  :total-rows="brand_totalRows"
                  :per-page="brand_perPage"
                  v-model="brand_currentPage"
                  class="my-0 gull-pagination align-items-center"
                  align="center"
                  first-text
                  last-text
                >
                  <p class="list-arrow m-0" slot="prev-text">
                    <i class="i-Arrow-Left text-40"></i>
                  </p>
                  <p class="list-arrow m-0" slot="next-text">
                    <i class="i-Arrow-Right text-40"></i>
                  </p>
                </b-pagination>
              </b-col>
            </b-row>
          </div>
        </b-sidebar>

        <!-- Sidebar category -->
        <b-sidebar
          id="sidebar-category"
          :title="$t('ListofCategory')"
          bg-variant="white"
          right
          shadow
        >
          <div class="px-3 py-2">
            <b-row>
              <div class="col-md-12 d-flex flex-row flex-wrap bd-highlight list-item mt-2">
                <div
                  @click="getAllCategory()"
                  :class="{ 'brand-Active' : category_id == ''}"
                  class="card o-hidden bd-highlight m-1"
                >
                  <div class="list-thumb d-flex">
                    <img alt :src="'/images/no-image.png'">
                  </div>
                  <div class="flex-grow-1 d-bock">
                    <div
                      class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center"
                    >
                      <div class="item-title">{{$t('All_Category')}}</div>
                    </div>
                  </div>
                </div>
                <div
                  class="card o-hidden bd-highlight m-1"
                  v-for="category in paginated_Category"
                  :key="category.id"
                  @click="Products_by_Category(category.id)"
                  :class="{ 'brand-Active' : category.id === category_id}"
                >
                  <img alt :src="'/images/no-image.png'">
                  <div class="flex-grow-1 d-bock">
                    <div
                      class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center"
                    >
                      <div class="item-title">{{category.name}}</div>
                    </div>
                  </div>
                </div>
              </div>
            </b-row>

            <b-row>
              <b-col md="12" class="mt-4">
                <b-pagination
                  @change="Category_onPageChanged"
                  :total-rows="category_totalRows"
                  :per-page="category_perPage"
                  v-model="category_currentPage"
                  class="my-0 gull-pagination align-items-center"
                  align="center"
                  first-text
                  last-text
                >
                  <p class="list-arrow m-0" slot="prev-text">
                    <i class="i-Arrow-Left text-40"></i>
                  </p>
                  <p class="list-arrow m-0" slot="next-text">
                    <i class="i-Arrow-Right text-40"></i>
                  </p>
                </b-pagination>
              </b-col>
            </b-row>
          </div>
        </b-sidebar>

        <!-- Modal Show Invoice POS-->
        <b-modal hide-footer size="sm" scrollable id="Show_invoice" :title="$t('Invoice_POS')">
          <div id="invoice-POS">
            <div style="max-width:400px;margin:0px auto">
              <div class="info">
                <h2 class="text-center">{{invoice_pos.setting.CompanyName}}</h2>
                <p>
                  <span>{{$t('date')}} : {{invoice_pos.sale.date}} <br></span>
                  <span v-show="pos_settings.show_address">{{$t('Adress')}} : {{invoice_pos.setting.CompanyAdress}} <br></span>
                  <span v-show="pos_settings.show_email">{{$t('Email')}} : {{invoice_pos.setting.email}} <br></span>
                  <span v-show="pos_settings.show_phone">{{$t('Phone')}} : {{invoice_pos.setting.CompanyPhone}} <br></span>
                  <span v-show="pos_settings.show_customer">{{$t('Customer')}} : {{invoice_pos.sale.client_name}} <br></span>
                </p>
              </div>

              <table class="table_data">
                <tbody>
                  <tr v-for="detail_invoice in invoice_pos.details">
                    <td colspan="3">
                      <span>
                        {{detail_invoice.name}}
                        <br>
                        {{formatNumber(detail_invoice.quantity,2)}} {{detail_invoice.unit_sale}} x {{formatNumber(detail_invoice.total/detail_invoice.quantity,2)}}
                      </span>
                    </td>
                    <td
                      style="text-align:right;vertical-align:bottom"
                    >{{formatNumber(detail_invoice.total,2)}}</td>
                  </tr>

                  <tr style="margin-top:10px">
                    <td colspan="3" class="total">{{$t('OrderTax')}}</td>
                    <td style="text-align:right;" class="total">{{invoice_pos.symbol}} {{formatNumber(invoice_pos.sale.taxe ,2)}} ({{formatNumber(invoice_pos.sale.tax_rate,2)}} %)</td>
                  </tr>

                  <tr style="margin-top:10px">
                    <td colspan="3" class="total">{{$t('Discount')}}</td>
                    <td style="text-align:right;" class="total">{{invoice_pos.symbol}} {{formatNumber(invoice_pos.sale.discount ,2)}}</td>
                  </tr>

                  <tr style="margin-top:10px">
                    <td colspan="3" class="total">{{$t('Total')}}</td>
                    <td
                      style="text-align:right;"
                      class="total"
                    >{{invoice_pos.symbol}} {{formatNumber(invoice_pos.sale.GrandTotal ,2)}}</td>
                  </tr>

                  <tr v-show="invoice_pos.sale.paid_amount < invoice_pos.sale.GrandTotal">
                    <td colspan="3" class="total">{{$t('Paid')}}</td>
                    <td
                      style="text-align:right;"
                      class="total"
                    >{{invoice_pos.symbol}} {{formatNumber(invoice_pos.sale.paid_amount ,2)}}</td>
                  </tr>

                  <tr v-show="invoice_pos.sale.paid_amount < invoice_pos.sale.GrandTotal">
                    <td colspan="3" class="total">{{$t('Due')}}</td>
                    <td
                      style="text-align:right;"
                      class="total"
                    >{{invoice_pos.symbol}} {{parseFloat(invoice_pos.sale.GrandTotal - invoice_pos.sale.paid_amount).toFixed(2)}}</td>
                  </tr>
                </tbody>
              </table>

              <table
                class="change mt-3"
                style=" font-size: 10px;"
                v-show="invoice_pos.sale.paid_amount > 0"
              >
                <thead>
                  <tr style="background: #eee; ">
                    <th style="text-align: left;" colspan="1">{{$t('PayeBy')}}:</th>
                    <th style="text-align: center;" colspan="2">{{$t('Amount')}}:</th>
                    <th style="text-align: right;" colspan="1">{{$t('Change')}}:</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="payment_pos in payments">
                    <td style="text-align: left;" colspan="1">{{payment_pos.Reglement}}</td>
                    <td
                      style="text-align: center;"
                      colspan="2"
                    >{{formatNumber(payment_pos.montant ,2)}}</td>
                    <td
                      style="text-align: right;"
                      colspan="1"
                    >{{formatNumber(payment_pos.change ,2)}}</td>
                  </tr>
                </tbody>
              </table>

              <div id="legalcopy" class="ml-2">
                <p class="legal" v-show="pos_settings.show_note">
                  <strong>{{pos_settings.note_customer}}</strong>
                </p>
                <div id="bar" v-show="pos_settings.show_barcode">
                  <barcode
                    class="barcode"
                    :format="barcodeFormat"
                    :value="invoice_pos.sale.Ref"
                    textmargin="0"
                    fontoptions="bold"
                    fontSize="15"
                    height="25"
                    width="1"
                  ></barcode>
                </div>
              </div>
            </div>
          </div>
          <button @click="print_pos()" class="btn btn-outline-primary">
            <i class="i-Billing"></i>
            {{$t('print')}}
          </button>
        </b-modal>

        <!-- Modal Add Payment-->
        <validation-observer ref="Add_payment">
          <b-modal hide-footer size="lg" id="Add_Payment" :title="$t('AddPayment')">
            <b-form @submit.prevent="Submit_Payment">
              <b-row>
                <b-col md="6">
                  <b-row>
                    <!-- Received  Amount  -->
                    <b-col lg="12" md="12" sm="12">
                      <validation-provider
                        name="Received Amount"
                        :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Received_Amount')">
                          <b-form-input
                            @keyup="Verified_Received_Amount(payment.received_amount)"
                            label="Received_Amount"
                            :placeholder="$t('Received_Amount')"
                            v-model.number="payment.received_amount"
                            :state="getValidationState(validationContext)"
                            aria-describedby="Received_Amount-feedback"
                          ></b-form-input>
                          <b-form-invalid-feedback
                            id="Received_Amount-feedback"
                          >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>

                    <!-- Paying  Amount  -->
                    <b-col lg="12" md="12" sm="12">
                      <validation-provider
                        name="Paying Amount"
                        :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                        v-slot="validationContext"
                      >
                        <b-form-group :label="$t('Paying_Amount')">
                          <b-form-input
                            label="Paying_Amount"
                            @keyup="Verified_paidAmount(payment.amount)"
                            :placeholder="$t('Paying_Amount')"
                            v-model.number="payment.amount"
                            :state="getValidationState(validationContext)"
                            aria-describedby="Paying_Amount-feedback"
                          ></b-form-input>
                          <b-form-invalid-feedback
                            id="Paying_Amount-feedback"
                          >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>

                    <!-- change  Amount  -->
                    <b-col lg="12" md="12" sm="12">
                      <label>{{$t('Change')}} :</label>
                      <p
                        class="change_amount"
                      >{{parseFloat(payment.received_amount - payment.amount).toFixed(2)}}</p>
                    </b-col>

                    <!-- Payment choice -->
                    <b-col lg="12" md="12" sm="12">
                      <validation-provider name="Payment choice" :rules="{ required: true}">
                        <b-form-group slot-scope="{ valid, errors }" :label="$t('Paymentchoice')">
                          <v-select
                            :class="{'is-invalid': !!errors.length}"
                            :state="errors[0] ? false : (valid ? true : null)"
                            v-model="payment.Reglement"
                            @input="Selected_PaymentMethod"
                            :reduce="label => label.value"
                            :placeholder="$t('PleaseSelect')"
                            :options="
                              [
                              {label: 'Cash', value: 'Cash'},
                              {label: 'credit card', value: 'credit card'},
                              {label: 'cheque', value: 'cheque'},
                              {label: 'Western Union', value: 'Western Union'},
                              {label: 'bank transfer', value: 'bank transfer'},
                              {label: 'other', value: 'other'},
                              ]"
                          ></v-select>
                          <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>

                    <b-col md="12" v-if="payment.Reglement == 'credit card'">
                      <form id="payment-form">
                        <label
                          for="card-element"
                          class="leading-7 text-sm text-gray-600"
                        >{{$t('Credit_Card_Info')}}</label>
                        <div id="card-element">
                          <!-- Elements will create input elements here -->
                        </div>
                        <!-- We'll put the error messages in this element -->
                        <div id="card-errors" class="is-invalid" role="alert"></div>
                      </form>
                    </b-col>

                    <!-- Note -->
                    <b-col lg="12" md="12" sm="12" class="mt-2">
                      <b-form-group :label="$t('Note')">
                        <b-form-textarea
                          id="textarea"
                          v-model="payment.notes"
                          rows="3"
                          max-rows="6"
                        ></b-form-textarea>
                      </b-form-group>
                    </b-col>
                  </b-row>
                </b-col>

                <b-col md="6">
                  <b-card>
                    <b-list-group>
                      <b-list-group-item class="d-flex justify-content-between align-items-center">
                        {{$t('TotalProducts')}}
                        <b-badge variant="primary" pill>{{details.length}}</b-badge>
                      </b-list-group-item>

                      <b-list-group-item class="d-flex justify-content-between align-items-center">
                        {{$t('OrderTax')}}
                        <span
                          class="font-weight-bold"
                        >{{currentUser.currency}} {{sale.TaxNet.toFixed(2)}} ({{sale.tax_rate}} %)</span>
                      </b-list-group-item>
                      <b-list-group-item class="d-flex justify-content-between align-items-center">
                        {{$t('Discount')}}
                        <span
                          class="font-weight-bold"
                        >{{currentUser.currency}} {{sale.discount.toFixed(2)}}</span>
                      </b-list-group-item>

                      <b-list-group-item class="d-flex justify-content-between align-items-center">
                        {{$t('Shipping')}}
                        <span
                          class="font-weight-bold"
                        >{{currentUser.currency}} {{sale.shipping.toFixed(2)}}</span>
                      </b-list-group-item>

                      <b-list-group-item class="d-flex justify-content-between align-items-center">
                        {{$t('Total')}}
                        <span
                          class="font-weight-bold"
                        >{{currentUser.currency}} {{GrandTotal.toFixed(2)}}</span>
                      </b-list-group-item>
                    </b-list-group>
                  </b-card>
                </b-col>

                <b-col md="12" class="mt-3">
                  <b-button
                    variant="primary"
                    type="submit"
                    :disabled="paymentProcessing"
                  >{{$t('submit')}}</b-button>
                  <div v-once class="typo__p" v-if="paymentProcessing">
                    <div class="spinner sm spinner-primary mt-3"></div>
                  </div>
                </b-col>
              </b-row>
            </b-form>
          </b-modal>
        </validation-observer>

        <validation-observer ref="Create_Customer">
          <b-modal hide-footer size="lg" id="New_Customer" :title="$t('Add')">
            <b-form @submit.prevent="Submit_Customer">
              <b-row>
                <!-- Customer Name -->
                <b-col md="6" sm="12">
                  <validation-provider
                    name="Name Customer"
                    :rules="{ required: true}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('CustomerName')">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="name-feedback"
                        label="name"
                        v-model="client.name"
                      ></b-form-input>
                      <b-form-invalid-feedback id="name-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Customer Email -->
                <b-col md="6" sm="12">
                  <validation-provider
                    name="Email customer"
                    :rules="{ required: true}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Email')">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Email-feedback"
                        label="Email"
                        v-model="client.email"
                      ></b-form-input>
                      <b-form-invalid-feedback id="Email-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Customer Phone -->
                <b-col md="6" sm="12">
                  <validation-provider
                    name="Phone Customer"
                    :rules="{ required: true}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Phone')">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Phone-feedback"
                        label="Phone"
                        v-model="client.phone"
                      ></b-form-input>
                      <b-form-invalid-feedback id="Phone-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Customer Country -->
                <b-col md="6" sm="12">
                  <validation-provider
                    name="Country customer"
                    :rules="{ required: true}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Country')">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Country-feedback"
                        label="Country"
                        v-model="client.country"
                      ></b-form-input>
                      <b-form-invalid-feedback
                        id="Country-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Customer City -->
                <b-col md="6" sm="12">
                  <validation-provider
                    name="City Customer"
                    :rules="{ required: true}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('City')">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="City-feedback"
                        label="City"
                        v-model="client.city"
                      ></b-form-input>
                      <b-form-invalid-feedback id="City-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Customer Adress -->
                <b-col md="6" sm="12">
                  <validation-provider
                    name="Adress customer"
                    :rules="{ required: true}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Adress')">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Adress-feedback"
                        label="Adress"
                        v-model="client.adresse"
                      ></b-form-input>
                      <b-form-invalid-feedback
                        id="Adress-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <b-col md="12" class="mt-3">
                  <b-button variant="primary" type="submit">{{$t('submit')}}</b-button>
                </b-col>
              </b-row>
            </b-form>
          </b-modal>
        </validation-observer>
      </b-row>
    </div>
  </div>
</template>

<script>
import NProgress from "nprogress";
import { mapActions, mapGetters } from "vuex";
import vueEasyPrint from "vue-easy-print";
import VueBarcode from "vue-barcode";
import FlagIcon from "vue-flag-icon";
import Util from "./../../../utils";
import { loadStripe } from "@stripe/stripe-js";

export default {
  components: {
    vueEasyPrint,
    barcode: VueBarcode,
    FlagIcon
  },
  metaInfo: {
    title: "POS"
  },
  data() {
    return {
      langs: [
        "en",
        "fr",
        "ar",
        "de",
        "es",
        "it",
        "Ind",
        "thai",
        "tr_ch",
        "sm_ch",
        "tur",
        "ru",
        "hn",
        "vn",
        "kr",
      ],
      stripe: {},
      stripe_key: "",
      cardElement: {},
      paymentProcessing: false,
      payment: {
        amount: "",
        received_amount: "",
        Reglement: "",
        notes: ""
      },
      focused: false,
      timer:null,
      search_input:'',
      product_filter:[],
      isLoading: true,
      GrandTotal: 0,
      total: 0,
      Ref: "",
      clients: [],
      units: [],
      warehouses: [],
      payments: [],
      products: [],
      products_pos: [],
      details: [],
      detail: {},
      categories: [],
      brands: [],
      pos_settings:{},
      product_currentPage: 1,
      paginated_Products: "",
      product_perPage: 8,
      product_totalRows: "",
      paginated_Brands: "",
      brand_currentPage: 1,
      brand_perPage: 3,
      paginated_Category: "",
      category_currentPage: 1,
      category_perPage: 3,
      barcodeFormat: "CODE128",
      invoice_pos: {
        sale: {
          Ref: "",
          client_name: "",
          discount: "",
          taxe: "",
          date: "",
          tax_rate: "",
          shipping: "",
          GrandTotal: "",
          paid_amount: ""
        },
        
        details: [],
        setting: {
          logo: "",
          CompanyName: "",
          CompanyAdress: "",
          email: "",
          CompanyPhone: ""
        }
      },

      sale: {
        warehouse_id: "",
        client_id: "",
        tax_rate: 0,
        shipping: 0,
        discount: 0,
        TaxNet: 0
      },
      client: {
        id: "",
        name: "",
        code: "",
        email: "",
        phone: "",
        country: "",
        city: "",
        adresse: ""
      },
      category_id: "",
      brand_id: "",
      product: {
        id: "",
        code: "",
        current: "",
        quantity: "",
        check_qty: "",
        discount: "",
        DiscountNet: "",
        discount_Method: "",
        sale_unit_id: "",
        fix_stock: "",
        fix_price: "",
        name: "",
        unitSale: "",
        Net_price: "",
        Unit_price: "",
        Total_price: "",
        subtotal: "",
        product_id: "",
        detail_id: "",
        taxe: "",
        tax_percent: "",
        tax_method: "",
        product_variant_id: ""
      },
      sound: "/audio/Beep.wav",
      audio: new Audio("/audio/Beep.wav")
    };
  },

  computed: {
    ...mapGetters(["currentUser", "currentUserPermissions"]),

    brand_totalRows() {
      return this.brands.length;
    },
    category_totalRows() {
      return this.categories.length;
    }
  },

  mounted() {
    this.changeSidebarProperties();
    this.paginate_products(this.product_perPage, 0);
  },

  methods: {
    ...mapActions(["changeSidebarProperties", "changeThemeMode", "logout"]),
    ...mapGetters(["currentUser"]),
    logoutUser() {
      this.$store.dispatch("logout");
    },

    async loadStripe_payment() {
      this.stripe = await loadStripe(`${this.stripe_key}`);
      const elements = this.stripe.elements();

      this.cardElement = elements.create("card", {
        classes: {
          base:
            "bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 p-3 leading-8 transition-colors duration-200 ease-in-out"
        }
      });

      this.cardElement.mount("#card-element");
    },

     handleFocus() {
      this.focused = true
    },

    handleBlur() {
      this.focused = false
    },

    //---------------------- Event Select Payment Method ------------------------------\\

    Selected_PaymentMethod(value) {
      if (value == "credit card") {
        setTimeout(() => {
          this.loadStripe_payment();
        }, 500);
      }
    },

    SetLocal(locale) {
      this.$i18n.locale = locale;
      this.$store.dispatch("language/setLanguage", locale);
      Fire.$emit("ChangeLanguage");
    },

    handleFullScreen() {
      Util.toggleFullScreen();
    },
    logoutUser() {
      this.logout();
    },

    // ------------------------ Paginate Products --------------------\\
    Product_paginatePerPage() {
      this.paginate_products(this.product_perPage, 0);
    },

    paginate_products(pageSize, pageNumber) {
      let itemsToParse = this.products;
      this.paginated_Products = itemsToParse.slice(
        pageNumber * pageSize,
        (pageNumber + 1) * pageSize
      );
    },

    Product_onPageChanged(page) {
      this.paginate_products(this.product_perPage, page - 1);
      this.getProducts(page);
    },

    // ------------------------ Paginate Brands --------------------\\

    BrandpaginatePerPage() {
      this.paginate_Brands(this.brand_perPage, 0);
    },

    paginate_Brands(pageSize, pageNumber) {
      let itemsToParse = this.brands;
      this.paginated_Brands = itemsToParse.slice(
        pageNumber * pageSize,
        (pageNumber + 1) * pageSize
      );
    },

    BrandonPageChanged(page) {
      this.paginate_Brands(this.brand_perPage, page - 1);
    },

    // ------------------------ Paginate Categories --------------------\\

    Category_paginatePerPage() {
      this.paginate_Category(this.category_perPage, 0);
    },

    paginate_Category(pageSize, pageNumber) {
      let itemsToParse = this.categories;
      this.paginated_Category = itemsToParse.slice(
        pageNumber * pageSize,
        (pageNumber + 1) * pageSize
      );
    },

    Category_onPageChanged(page) {
      this.paginate_Category(this.category_perPage, page - 1);
    },

    //--- Submit Validate Create Sale
    Submit_Pos() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.$refs.create_pos.validate().then(success => {
        if (!success) {
          NProgress.done();
          if (this.sale.client_id == "" || this.sale.client_id === null) {
            this.makeToast(
              "danger",
              this.$t("Choose_Customer"),
              this.$t("Failed")
            );
          } else if (
            this.sale.warehouse_id == "" ||
            this.sale.warehouse_id === null
          ) {
            this.makeToast(
              "danger",
              this.$t("Choose_Warehouse"),
              this.$t("Failed")
            );
          } else {
            this.makeToast(
              "danger",
              this.$t("Please_fill_the_form_correctly"),
              this.$t("Failed")
            );
          }
        } else {
          if (this.verifiedForm()) {
            Fire.$emit("pay_now");
          } else {
            NProgress.done();
          }
        }
      });
    },
    //---Submit Validation Update Detail
    submit_Update_Detail() {
      this.$refs.Update_Detail.validate().then(success => {
        if (!success) {
          return;
        } else {
          this.Update_Detail();
        }
      });
    },

    //------ Validate Form Submit_Payment
    Submit_Payment() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.$refs.Add_payment.validate().then(success => {
        if (!success) {
          // Complete the animation of theprogress bar.
          NProgress.done();
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
            if (this.payment.amount > this.payment.received_amount) {
              this.makeToast(
                "warning",
                this.$t("Paying_amount_is_greater_than_Received_amount"),
                this.$t("Warning")
              );
              this.payment.received_amount = 0;
               NProgress.done();
            }
            else if (this.payment.amount > this.GrandTotal) {
              this.makeToast(
                "warning",
                this.$t("Paying_amount_is_greater_than_Grand_Total"),
                this.$t("Warning")
              );
              this.payment.amount = 0;
               NProgress.done();
            }else{
              this.CreatePOS();
            }
       
        }
      });
    },

    //------------- Submit Validation Create & Edit Customer
    Submit_Customer() {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.$refs.Create_Customer.validate().then(success => {
        if (!success) {
          NProgress.done();
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          this.Create_Client();
        }
      });
    },

    //---------------------------------------- Create new Customer -------------------------------\\
    Create_Client() {
      axios
        .post("clients", {
          name: this.client.name,
          email: this.client.email,
          phone: this.client.phone,
          country: this.client.country,
          city: this.client.city,
          adresse: this.client.adresse
        })
        .then(response => {
          NProgress.done();
          this.makeToast(
            "success",
            this.$t("Create.TitleCustomer"),
            this.$t("Success")
          );
          this.Get_Client_Without_Paginate();
          this.$bvModal.hide("New_Customer");
        })
        .catch(error => {
          NProgress.done();
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //------------------------------ New Model (create Customer) -------------------------------\\
    New_Client() {
      this.reset_Form_client();
      this.$bvModal.show("New_Customer");
    },

    //-------------------------------- reset Form -------------------------------\\
    reset_Form_client() {
      this.client = {
        id: "",
        name: "",
        email: "",
        phone: "",
        country: "",
        city: "",
        adresse: ""
      };
    },

    //------------------------------------ Get Clients Without Paginate -------------------------\\
    Get_Client_Without_Paginate() {
      axios
        .get("Get_Clients_Without_Paginate")
        .then(({ data }) => (this.clients = data));
    },

    //---Validate State Fields
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    //---------------------- Event Select Warehouse ------------------------------\\
    Selected_Warehouse(value) {
      this.search_input= '';
      this.product_filter = [];
      this.Get_Products_By_Warehouse(value);
      this.getProducts(1);
    },

    //------------------------------------ Get Products By Warehouse -------------------------\\

    Get_Products_By_Warehouse(id) {
      // Start the progress bar.
        NProgress.start();
        NProgress.set(0.1);
      axios
        .get("Products/Warehouse/" + id + "?stock=" + 1)
         .then(response => {
            this.products_pos = response.data;
             NProgress.done();

            })
          .catch(error => {
          });
    },

    //----------------------------------------- Add Detail of Sale -------------------------\\
    add_product(code) {
      this.audio.play();
      if (this.details.some(detail => detail.code === code)) {
        this.increment_qty_scanner(code);
        // this.makeToast("warning", this.$t("AlreadyAdd"), this.$t("Warning"));
        // Complete the animation of theprogress bar.
        NProgress.done();
      } else {
        if (this.details.length > 0) {
          this.order_detail_id();
        } else if (this.details.length === 0) {
          this.product.detail_id = 1;
        }
        this.details.push(this.product);
      }
    },

    //-------------------------------- order detail id -------------------------\\
    order_detail_id() {
      this.product.detail_id = 0;
      var len = this.details.length;
      this.product.detail_id = this.details[len - 1].detail_id + 1;
    },

    //---------------------- Get_sales_units ------------------------------\\
    Get_sales_units(value) {
      axios
        .get("Get_sales_units?id=" + value)
        .then(({ data }) => (this.units = data));
    },

    //------ Show Modal Update Detail Product
    Modal_Update_Detail(detail) {
      this.detail = {};
      this.Get_sales_units(detail.product_id);
      this.detail.detail_id = detail.detail_id;
      this.detail.sale_unit_id = detail.sale_unit_id;
      this.detail.name = detail.name;
      this.detail.Unit_price = detail.Unit_price;
      this.detail.fix_price = detail.fix_price;
      this.detail.fix_stock = detail.fix_stock;
      this.detail.current = detail.current;
      this.detail.tax_method = detail.tax_method;
      this.detail.discount_Method = detail.discount_Method;
      this.detail.discount = detail.discount;
      this.detail.quantity = detail.quantity;
      this.detail.tax_percent = detail.tax_percent;
      this.$bvModal.show("form_Update_Detail");
    },

    //------ Submit Update Detail Product

    Update_Detail() {
      for (var i = 0; i < this.details.length; i++) {
        if (this.details[i].detail_id === this.detail.detail_id) {
          // this.convert_unit();
          for (var k = 0; k < this.units.length; k++) {
            if (this.units[k].id == this.detail.sale_unit_id) {
              if (this.units[k].operator == "/") {
                this.details[i].current =
                  this.detail.fix_stock * this.units[k].operator_value;
                this.details[i].unitSale = this.units[k].ShortName;
              } else {
                this.details[i].current =
                  this.detail.fix_stock / this.units[k].operator_value;
                this.details[i].unitSale = this.units[k].ShortName;
              }
            }
          }

          if (this.details[i].current < this.details[i].quantity) {
            this.details[i].quantity = this.details[i].current;
          } else {
            this.details[i].quantity = 1;
          }

          this.details[i].Unit_price = this.detail.Unit_price;
          this.details[i].tax_percent = this.detail.tax_percent;
          this.details[i].tax_method = this.detail.tax_method;
          this.details[i].discount_Method = this.detail.discount_Method;
          this.details[i].discount = this.detail.discount;
          this.details[i].sale_unit_id = this.detail.sale_unit_id;

          if (this.details[i].discount_Method == "2") {
            //Fixed
            this.details[i].DiscountNet = this.details[i].discount;
          } else {
            //Percentage %
            this.details[i].DiscountNet = parseFloat(
              (this.details[i].Unit_price * this.details[i].discount) / 100
            );
          }

          if (this.details[i].tax_method == "1") {
            //Exclusive
            this.details[i].Net_price = parseFloat(
              this.details[i].Unit_price - this.details[i].DiscountNet
            );

            this.details[i].taxe = parseFloat(
              (this.details[i].tax_percent *
                (this.details[i].Unit_price - this.details[i].DiscountNet)) /
                100
            );
            this.details[i].Total_price = parseFloat(
              this.details[i].Net_price + this.details[i].taxe
            );
          } else {
            //Inclusive
            this.details[i].Net_price = parseFloat(
              (this.details[i].Unit_price - this.details[i].DiscountNet) /
                (this.details[i].tax_percent / 100 + 1)
            );

            this.details[i].taxe = parseFloat(
              this.details[i].Unit_price -
                this.details[i].Net_price -
                this.details[i].DiscountNet
            );
            this.details[i].Total_price = parseFloat(
              this.details[i].Net_price + this.details[i].taxe
            );
          }

          this.$forceUpdate();
        }
      }
      this.CaclulTotal();
      this.$bvModal.hide("form_Update_Detail");
    },

    //-- check Qty of  details order if Null or zero
    verifiedForm() {
      if (this.details.length <= 0) {
        this.makeToast(
          "warning",
          this.$t("AddProductToList"),
          this.$t("Warning")
        );
        return false;
      } else {
        var count = 0;
        for (var i = 0; i < this.details.length; i++) {
          if (
            this.details[i].quantity == "" ||
            this.details[i].quantity === 0
          ) {
            count += 1;
          }
        }

        if (count > 0) {
          this.makeToast("warning", this.$t("AddQuantity"), this.$t("Warning"));

          return false;
        } else {
          return true;
        }
      }
    },

    //------------------------------ Print -------------------------\\
    print_pos() {
      var divContents = document.getElementById("invoice-POS").innerHTML;
      var a = window.open("", "", "height=500, width=500");
      a.document.write(
        '<link rel="stylesheet" href="/css/pos_print.css"><html>'
      );
      a.document.write("<body >");
      a.document.write(divContents);
      a.document.write("</body></html>");
      a.document.close();
      a.print();
    },

    //-------------------------------- Invoice POS ------------------------------\\
    Invoice_POS(id) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get("Sales/Print_Invoice/" + id)
        .then(response => {
          this.invoice_pos = response.data;
          this.payments = response.data.payments;
          this.pos_settings = response.data.pos_settings;
          setTimeout(() => {
            // Complete the animation of the  progress bar.
            NProgress.done();
            this.$bvModal.show("Show_invoice");
          }, 500);
          setTimeout(() => this.print_pos(), 1000);
        })
        .catch(() => {
          // Complete the animation of the  progress bar.
          setTimeout(() => NProgress.done(), 500);
        });
    },

    //----------------------------------Process Payment ------------------------------\\

    async processPayment() {
      this.paymentProcessing = true;

      const { token, error } = await this.stripe.createToken(this.cardElement);

      if (error) {
        this.paymentProcessing = false;
        NProgress.done();
        this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
      } else {

        
        axios
          .post("pos/CreatePOS", {
            client_id: this.sale.client_id,
            warehouse_id: this.sale.warehouse_id,
            tax_rate: this.sale.tax_rate?this.sale.tax_rate:0,
            TaxNet: this.sale.TaxNet?this.sale.TaxNet:0,
            discount: this.sale.discount?this.sale.discount:0,
            shipping: this.sale.shipping?this.sale.shipping:0,
            details: this.details,
            GrandTotal: this.GrandTotal,
            payment: this.payment,
            amount : parseFloat(this.payment.amount).toFixed(2),
            received_amount : parseFloat(this.payment.received_amount).toFixed(2),
            change: parseFloat(this.payment.received_amount - this.payment.amount).toFixed(2),
            token: token.id
          })
          .then(response => {
            this.paymentProcessing = false;
            if (response.data.success === true) {
              // Complete the animation of theprogress bar.
              NProgress.done();
              this.Invoice_POS(response.data.id);
              this.$bvModal.hide("Add_Payment");
              this.Reset_Pos();
            }
          })
          .catch(error => {
            this.paymentProcessing = false;
            // Complete the animation of theprogress bar.
            NProgress.done();
            this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          });
      }
    },

    //----------------------------------Create POS ------------------------------\\
    CreatePOS() {
      NProgress.start();
      NProgress.set(0.1);
      if (this.payment.Reglement == "credit card") {
        if (this.stripe_key != "") {
          this.processPayment();
        } else {
          this.makeToast(
            "danger",
            this.$t("credit_card_account_not_available"),
            this.$t("Failed")
          );
          NProgress.done();
        }
      } else {
        this.paymentProcessing = true;
        axios
          .post("pos/CreatePOS", {
            client_id: this.sale.client_id,
            warehouse_id: this.sale.warehouse_id,
            tax_rate: this.sale.tax_rate?this.sale.tax_rate:0,
            TaxNet: this.sale.TaxNet?this.sale.TaxNet:0,
            discount: this.sale.discount?this.sale.discount:0,
            shipping: this.sale.shipping?this.sale.shipping:0,
            details: this.details,
            GrandTotal: this.GrandTotal,
            payment: this.payment,
            amount : parseFloat(this.payment.amount).toFixed(2),
            received_amount : parseFloat(this.payment.received_amount).toFixed(2),
            change: parseFloat(this.payment.received_amount - this.payment.amount).toFixed(2),
          })
          .then(response => {
            if (response.data.success === true) {
              // Complete the animation of theprogress bar.
              NProgress.done();
              this.paymentProcessing = false;
              this.Invoice_POS(response.data.id);
              this.$bvModal.hide("Add_Payment");
              this.Reset_Pos();
            }
          })
          .catch(error => {
            // Complete the animation of theprogress bar.
            NProgress.done();
            this.paymentProcessing = false;
            this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          });
      }
    },

    //------------------------------Formetted Numbers -------------------------\\
    formatNumber(number, dec) {
      const value = (typeof number === "string"
        ? number
        : number.toString()
      ).split(".");
      if (dec <= 0) return value[0];
      let formated = value[1] || "";
      if (formated.length > dec)
        return `${value[0]}.${formated.substr(0, dec)}`;
      while (formated.length < dec) formated += "0";
      return `${value[0]}.${formated}`;
    },

    //---------------------------------Get Product Details ------------------------\\

    Get_Product_Details(product, product_id) {
      axios.get("Products/" + product_id).then(response => {
        this.product.discount = 0;
        this.product.DiscountNet = 0;
        this.product.discount_Method = "2";
        this.product.product_id = response.data.id;
        this.product.name = response.data.name;
        this.product.Net_price = response.data.Net_price;
        this.product.Total_price = response.data.Total_price;
        this.product.Unit_price = response.data.Unit_price;
        this.product.taxe = response.data.tax_price;
        this.product.tax_method = response.data.tax_method;
        this.product.tax_percent = response.data.tax_percent;
        this.product.unitSale = response.data.unitSale;
        this.product.product_variant_id = product.product_variant_id;
        this.product.code = product.code;
        this.product.fix_price = response.data.fix_price;
        this.product.sale_unit_id = response.data.sale_unit_id;
        this.add_product(product.code);
        this.CaclulTotal();
        // Complete the animation of theprogress bar.
        NProgress.done();
      });
    },

    //----------- Calcul Total
    CaclulTotal() {
      this.total = 0;
      for (var i = 0; i < this.details.length; i++) {
        var tax = this.details[i].taxe * this.details[i].quantity;
        this.details[i].subtotal = parseFloat(
          this.details[i].quantity * this.details[i].Net_price + tax
        );

        this.total = parseFloat(this.total + this.details[i].subtotal);
      }
      const total_without_discount = parseFloat(
        this.total - this.sale.discount
      );
      this.sale.TaxNet = parseFloat(
        (total_without_discount * this.sale.tax_rate) / 100
      );
      this.GrandTotal = parseFloat(
        total_without_discount + this.sale.TaxNet + this.sale.shipping
      );

      var grand_total =  this.GrandTotal.toFixed(2);
      this.GrandTotal = parseFloat(grand_total);
    },

    //-------Verified QTY
    Verified_Qty(detail, id) {
      for (var i = 0; i < this.details.length; i++) {
        if (this.details[i].detail_id === id) {
          if (isNaN(detail.quantity)) {
            this.details[i].quantity = detail.current;
          }
          if (detail.quantity > detail.current) {
            this.makeToast("warning", this.$t("LowStock"), this.$t("Warning"));
            this.details[i].quantity = detail.current;
          } else {
            this.details[i].quantity = detail.quantity;
          }
        }
      }
      this.$forceUpdate();
      this.CaclulTotal();
    },

    //----------------------------------- Increment QTY with barcode scanner ------------------------------\\
    increment_qty_scanner(code) {
      for (var i = 0; i < this.details.length; i++) {
        if (this.details[i].code === code) {
          if (this.details[i].quantity + 1 > this.details[i].current) {
            this.makeToast("warning", this.$t("LowStock"), this.$t("Warning"));
          } else {
            this.details[i].quantity++;
          }
        }
      }
      this.CaclulTotal();
      this.$forceUpdate();
    },

    //----------------------------------- Increment QTY ------------------------------\\
    increment(id) {
      for (var i = 0; i < this.details.length; i++) {
        if (this.details[i].detail_id == id) {
          if (this.details[i].quantity + 1 > this.details[i].current) {
            this.makeToast("warning", this.$t("LowStock"), this.$t("Warning"));
          } else {
            this.details[i].quantity++;
          }
        }
      }
      this.CaclulTotal();
      this.$forceUpdate();
    },

    //----------------------------------- decrement QTY ------------------------------\\
    decrement(detail, id) {
      for (var i = 0; i < this.details.length; i++) {
        if (this.details[i].detail_id == id) {
          if (detail.quantity - 1 > detail.current || detail.quantity - 1 < 1) {
            this.makeToast("warning", this.$t("LowStock"), this.$t("Warning"));
          } else {
            this.details[i].quantity--;
          }
        }
      }
      this.CaclulTotal();
      this.$forceUpdate();
    },

  
    //---------- keyup OrderTax
    keyup_OrderTax() {
      if (isNaN(this.sale.tax_rate)) {
        this.sale.tax_rate = 0;
      } else if(this.sale.tax_rate == ''){
         this.sale.tax_rate = 0;
        this.CaclulTotal();
      }else {
        this.CaclulTotal();
      }
    },

    //---------- keyup Discount

    keyup_Discount() {
      if (isNaN(this.sale.discount)) {
        this.sale.discount = 0;
      } else if(this.sale.discount == ''){
         this.sale.discount = 0;
        this.CaclulTotal();
      }else {
        this.CaclulTotal();
      }
    },

    //---------- keyup Shipping

    keyup_Shipping() {
      if (isNaN(this.sale.shipping)) {
        this.sale.shipping = 0;
      } else if(this.sale.shipping == ''){
         this.sale.shipping = 0;
        this.CaclulTotal();
      }else {
        this.CaclulTotal();
      }
    },

    //---------- keyup paid Amount

    Verified_paidAmount() {
      if (isNaN(this.payment.amount)) {
        this.payment.amount = 0;
      } else {
        if (this.payment.amount > this.payment.received_amount) {
          this.makeToast(
            "warning",
            this.$t("Paying_amount_is_greater_than_Received_amount"),
            this.$t("Warning")
          );
          this.payment.amount = 0;
        } 
        else if (this.payment.amount > this.GrandTotal) {
          this.makeToast(
            "warning",
            this.$t("Paying_amount_is_greater_than_Grand_Total"),
            this.$t("Warning")
          );
          this.payment.amount = 0;
        }
      }
    },

    //---------- keyup Received Amount

    Verified_Received_Amount() {
      if (isNaN(this.payment.received_amount)) {
        this.payment.received_amount = 0;
      } 
    },

    //-----------------------------------Delete Detail Product ------------------------------\\
    delete_Product_Detail(id) {
      for (var i = 0; i < this.details.length; i++) {
        if (id === this.details[i].detail_id) {
          this.details.splice(i, 1);
          this.CaclulTotal();
        }
      }
    },

    //----------Reset Pos
    Reset_Pos() {
      this.details = [];
      this.product = {};
      this.payment = {
        amount: "",
        received_amount: "",
        Reglement: "",
        notes: "",
      };
      this.sale.tax_rate = 0;
      this.sale.TaxNet = 0;
      this.sale.shipping = 0;
      this.sale.discount = 0;
      this.GrandTotal = 0;
      this.total = 0;
      this.category_id = "";
      this.brand_id = "";
      this.getProducts(1);
    },

    //------------------------- get Result Value Search Product

    getResultValue(result) {
      return result.code + " " + "(" + result.name + ")";
    },

    //------------------------- Submit Search Product

    SearchProduct(result) {
      this.product = {};

      this.product.code = result.code;
      this.product.current = result.qte_sale;
      this.product.fix_stock = result.qte;
      if (result.qte_sale < 1) {
        this.product.quantity = result.qte_sale;
      } else {
        this.product.quantity = 1;
      }
      this.product.product_variant_id = result.product_variant_id;
      this.Get_Product_Details(result, result.id);

      this.search_input= '';
      this.product_filter = [];
    },


      // Search Products
    search(){

      if (this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
      }

      if (this.search_input.length < 1) {

        return this.product_filter= [];
      }
      if (this.sale.warehouse_id != "" &&  this.sale.warehouse_id != null) {
        this.timer = setTimeout(() => {
          const product_filter = this.products_pos.filter(product => product.code === this.search_input || product.barcode.includes(this.search_input));
            if(product_filter.length === 1){
                this.Check_Product_Exist(product_filter[0], product_filter[0].id);
            }else{
                this.product_filter=  this.products_pos.filter(product => {
                  return (
                    product.name.toLowerCase().includes(this.search_input.toLowerCase()) ||
                    product.code.toLowerCase().includes(this.search_input.toLowerCase()) ||
                    product.barcode.toLowerCase().includes(this.search_input.toLowerCase())
                    );
                });
            }
        }, 800);
      } else {
        this.makeToast(
          "warning",
          this.$t("SelectWarehouse"),
          this.$t("Warning")
        );
      }

    },

   

    //---------------------------------- Check if Product Exist in Order List ---------------------\\

    Check_Product_Exist(product, id) {
      // this.audio.play();
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.product = {};
      this.product.current = product.qte_sale;
      this.product.fix_stock = product.qte;
      if (product.qte_sale < 1) {
        this.product.quantity = product.qte_sale;
      } else {
        this.product.quantity = 1;
      }
      this.Get_Product_Details(product, id);
      NProgress.done();

      this.search_input= '';
      this.product_filter = [];
    },

    //--- Get Products by Category
    Products_by_Category(id) {
      this.category_id = id;
      this.getProducts(1);
    },

    //--- Get Products by Brand
    Products_by_Brands(id) {
      this.brand_id = id;
      this.getProducts(1);
    },

    //--- Get All Category
    getAllCategory() {
      this.category_id = "";
      this.getProducts(1);
    },

    //--- Get All Brands
    GetAllBrands() {
      this.brand_id = "";
      this.getProducts(1);
    },

    //------------------------------- Get Products with Filters ------------------------------\\
    getProducts(page = 1) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "GetProductsByParametre?page=" +
            page +
            "&category_id=" +
            this.category_id +
            "&brand_id=" +
            this.brand_id +
            "&warehouse_id=" +
            this.sale.warehouse_id +
            "&stock=" +
            1
        )
        .then(response => {
          this.products = response.data.products;
          this.product_totalRows = response.data.totalRows;
          this.Product_paginatePerPage();

          // Complete the animation of theprogress bar.
          NProgress.done();
        })
        .catch(response => {
          // Complete the animation of theprogress bar.
          NProgress.done();
        });
    },

    //---------------------------------------Get Elements ------------------------------\\
    GetElementsPos() {
      axios
        .get("pos/GetELementPos")
        .then(response => {
          this.clients = response.data.clients;
          this.warehouses = response.data.warehouses;
          this.categories = response.data.categories;
          this.brands = response.data.brands;
          // this.pos_settings = response.data.pos_settings;
          this.sale.warehouse_id = response.data.defaultWarehouse;
          this.sale.client_id = response.data.defaultClient;
          this.getProducts();
          if (response.data.defaultWarehouse != "") {
            this.Get_Products_By_Warehouse(response.data.defaultWarehouse);
          }
          this.paginate_Brands(this.brand_perPage, 0);
          this.paginate_Category(this.category_perPage, 0);
          this.stripe_key = response.data.stripe_key;
          this.isLoading = false;
        })
        .catch(response => {
          this.isLoading = false;
        });
    }
  },

  //-------------------- Created Function -----\\

  created() {
    this.GetElementsPos();
    Fire.$on("pay_now", () => {
      setTimeout(() => {
        this.payment.amount = this.formatNumber(this.GrandTotal, 2);
        this.payment.received_amount = this.formatNumber(this.GrandTotal, 2);

        this.payment.Reglement = "Cash";
        this.$bvModal.show("Add_Payment");
        // Complete the animation of theprogress bar.
        NProgress.done();
      }, 500);
    });
  }
};
</script>

<style>
.total {
  font-weight: bold;
  font-size: 14px;
  /* text-transform: uppercase; */
  /* height: 50px; */
}
</style>