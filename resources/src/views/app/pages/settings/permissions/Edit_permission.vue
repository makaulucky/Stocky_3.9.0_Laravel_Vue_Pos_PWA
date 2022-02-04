<template>
  <div class="main-content">
    <breadcumb :page="$t('Edit_Permission')" :folder="$t('Settings')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <validation-observer ref="Edit_Permission" v-if="!isLoading">
      <b-form @submit.prevent="Submit_Permission">
        <b-row>
          <b-col lg="12" md="12" sm="12">
            <b-card>
              <b-row>
                <!-- Role Name -->
                <b-col md="6">
                  <validation-provider
                    name="Role name"
                    :rules="{ required: true}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('RoleName')">
                      <b-form-input
                        :placeholder="$t('Enter_Role_Name')"
                        :state="getValidationState(validationContext)"
                        aria-describedby="Role-feedback"
                        v-model="role.name"
                      ></b-form-input>
                      <b-form-invalid-feedback id="Role-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Role description -->
                <b-col md="6">
                  <b-form-group :label="$t('RoleDescription')">
                    <b-form-input
                      :placeholder="$t('Enter_Role_Description')"
                      v-model="role.description"
                    ></b-form-input>
                  </b-form-group>
                </b-col>
              </b-row>

              <b-row class="mt-4">
                <!--Users -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-UserManagement
                        variant="transparent"
                      >{{$t('UserManagement')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-UserManagement "
                      :visible="true"
                      accordion="my-accordion1"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Users View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="users_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Users ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="users_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Users Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="users_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Users Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="users_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Users record view -->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="record_view"
                                >
                                <span>{{$t('ShowAll')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!--  Permissions -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Permissions
                        variant="transparent"
                      >{{$t('UserPermissions')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Permissions "
                      :visible="true"
                      accordion="my-accordion2"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Permissions View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="permissions_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Permissions ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="permissions_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Permissions Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="permissions_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Permissions Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="permissions_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!--  Products -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Products
                        variant="transparent"
                      >{{$t('Products')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Products"
                      :visible="true"
                      accordion="my-accordion3"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Products View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="products_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Products ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="products_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Products Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="products_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Products Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="products_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Products Barcode -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="barcode_view"
                                >
                                <span>{{$t('Barcode')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>

                            <!--Products Import -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="product_import"
                                >
                                <span>{{$t('import_products')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!--  Adjustment -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Adjustment
                        variant="transparent"
                      >{{$t('StockAdjustement')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Adjustment"
                      :visible="true"
                      accordion="my-accordion4"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Adjustment View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="adjustment_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Adjustment ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="adjustment_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Adjustment Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="adjustment_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Adjustment Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="adjustment_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!--  Transfer -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Transfer
                        variant="transparent"
                      >{{$t('StockTransfers')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Transfer"
                      :visible="true"
                      accordion="my-accordion5"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Transfer View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="transfer_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Transfer ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="transfer_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Transfer Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="transfer_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Transfer Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="transfer_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!--  Expense -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Expense
                        variant="transparent"
                      >{{$t('Expenses')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Expense"
                      :visible="true"
                      accordion="my-accordion6"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Expense View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="expense_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Expense ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="expense_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Expense Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="expense_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Expense Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="expense_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!-- Sales -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Sales
                        variant="transparent"
                      >{{$t('Sales')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Sales"
                      :visible="true"
                      accordion="my-accordion7"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Sales View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Sales_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Sales ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Sales_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Sales Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Sales_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Sales Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Sales_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Sales POS -->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Pos_view"
                                >
                                <span>{{$t('pointofsales')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!-- Purchases -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Purchases
                        variant="transparent"
                      >{{$t('Purchases')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Purchases"
                      :visible="true"
                      accordion="my-accordion8"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Purchases View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Purchases_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Purchases ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Purchases_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Purchases Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Purchases_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Purchases Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Purchases_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!-- Quotations -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Quotations
                        variant="transparent"
                      >{{$t('Quotations')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Quotations"
                      :visible="true"
                      accordion="my-accordion9"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Quotations View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Quotations_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Quotations ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Quotations_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Quotations Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Quotations_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Quotations Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Quotations_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!-- Sale Returns -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Return-Sale
                        variant="transparent"
                      >{{$t('SalesReturn')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Return-Sale"
                      :visible="true"
                      accordion="my-accordion10"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Return Sale View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Sale_Returns_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Return Sale ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Sale_Returns_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Return Sale Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Sale_Returns_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Return Sale Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Sale_Returns_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!-- Purchase Return -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Return-Purchase
                        variant="transparent"
                      >{{$t('PurchasesReturn')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Return-Purchase"
                      :visible="true"
                      accordion="my-accordion11"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Return Purchase View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Purchase_Returns_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Return Purchase ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Purchase_Returns_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Return Purchase Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Purchase_Returns_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Return Purchase Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Purchase_Returns_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!-- Payment Sales -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Payment-Sales
                        variant="transparent"
                      >{{$t('PaymentsSales')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Payment-Sales"
                      :visible="true"
                      accordion="my-accordion12"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Payment Sales View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="payment_sales_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Payment Sales ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="payment_sales_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Payment Sales Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="payment_sales_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Payment Sales Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="payment_sales_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!-- Payment Purchases -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Payment-Purchases
                        variant="transparent"
                      >{{$t('PaymentsPurchases')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Payment-Purchases"
                      :visible="true"
                      accordion="my-accordion13"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Payment Purchases View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="payment_purchases_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Payment Purchases ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="payment_purchases_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Payment Purchases Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="payment_purchases_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Payment Purchases Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="payment_purchases_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!-- Payment Returns -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Payment-Returns
                        variant="transparent"
                      >{{$t('PaymentsReturns')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Payment-Returns"
                      :visible="true"
                      accordion="my-accordion14"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Payment Returns View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="payment_returns_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Payment Returns ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="payment_returns_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Payment Returns Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="payment_returns_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Payment Returns Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="payment_returns_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!-- Customers -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Customers
                        variant="transparent"
                      >{{$t('Customers')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Customers"
                      :visible="true"
                      accordion="my-accordion15"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Customers View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Customers_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Customers ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Customers_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Customers Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Customers_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Customers Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Customers_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>

                            <!--Customers Import -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="customers_import"
                                >
                                <span>{{$t('Import_Customers')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!-- Suppliers -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Suppliers
                        variant="transparent"
                      >{{$t('Suppliers')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Suppliers"
                      :visible="true"
                      accordion="my-accordion16"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Suppliers View -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Suppliers_view"
                                >
                                <span>{{$t('View')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Suppliers ADD -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Suppliers_add"
                                >
                                <span>{{$t('Add')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Suppliers Edit -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Suppliers_edit"
                                >
                                <span>{{$t('Edit')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Suppliers Delete -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Suppliers_delete"
                                >
                                <span>{{$t('Del')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>

                            <!--Suppliers Import -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Suppliers_import"
                                >
                                <span>{{$t('Import_Suppliers')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!-- Reports -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Reports
                        variant="transparent"
                      >{{$t('Reports')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Reports"
                      :visible="true"
                      accordion="my-accordion17"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Reports_payments_Sales  -->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Reports_payments_Sales"
                                >
                                <span>{{$t('Reports_payments_Sales')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Reports_payments_Purchases  -->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Reports_payments_Purchases"
                                >
                                <span>{{$t('Reports_payments_Purchases')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Reports_payments_Sale_Return-->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Reports_payments_Sale_Returns"
                                >
                                <span>{{$t('Reports_payments_Sale_Return')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Reports_payments_Purchase_Return -->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Reports_payments_purchase_Return"
                                >
                                <span>{{$t('Reports_payments_Purchase_Return')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!-- Sales Reports -->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Reports_sales"
                                >
                                <span>{{$t('SalesReport')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Purchases Reports -->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Reports_purchase"
                                >
                                <span>{{$t('PurchasesReport')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!-- Customers Reports -->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Reports_customers"
                                >
                                <span>{{$t('CustomersReport')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Suppliers Reports -->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Reports_suppliers"
                                >
                                <span>{{$t('SuppliersReport')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Proft and Loss -->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Reports_profit"
                                >
                                <span>{{$t('ProfitandLoss')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Product Quantity Alerts -->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Reports_quantity_alerts"
                                >
                                <span>{{$t('ProductQuantityAlerts')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>

                            <!--Warehouse Stock Chart-->
                            <b-col md="12">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="Warehouse_report"
                                >
                                <span>{{$t('WarehouseStockChart')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>

                <!-- Settings -->
                <b-col md="4">
                  <b-card no-body class="ul-card__border-radius">
                    <b-card-header header-tag="header" class="p-1" role="tab">
                      <b-button
                        class="card-title mb-0"
                        block
                        href="#"
                        v-b-toggle.panel-Settings
                        variant="transparent"
                      >{{$t('Settings')}}</b-button>
                    </b-card-header>
                    <b-collapse
                      id="panel-Settings"
                      :visible="true"
                      accordion="my-accordion18"
                      role="tabpanel"
                    >
                      <b-card-body>
                        <b-card-text>
                          <b-row>
                            <!--Settings System -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="setting_system"
                                >
                                <span>{{$t('SystemSettings')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Category -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="category"
                                >
                                <span>{{$t('Categories')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Brand  -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input type="checkbox" checked v-model="permissions" value="brand">
                                <span>{{$t('Brand')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Currency  -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="currency"
                                >
                                <span>{{$t('Currencies')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Warehouse  -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input
                                  type="checkbox"
                                  checked
                                  v-model="permissions"
                                  value="warehouse"
                                >
                                <span>{{$t('Warehouses')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Unit  -->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input type="checkbox" checked v-model="permissions" value="unit">
                                <span>{{$t('Units')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                            <!--Backup-->
                            <b-col md="6">
                              <label class="checkbox checkbox-outline-primary">
                                <input type="checkbox" checked v-model="permissions" value="backup">
                                <span>{{$t('Backup')}}</span>
                                <span class="checkmark"></span>
                              </label>
                            </b-col>
                          </b-row>
                        </b-card-text>
                      </b-card-body>
                    </b-collapse>
                  </b-card>
                </b-col>
              </b-row>
              <!-- End row -->
              <b-col md="12">
                <b-button variant="primary" type="submit"  :disabled="SubmitProcessing">{{$t('submit')}}</b-button>
                  <div v-once class="typo__p" v-if="SubmitProcessing">
                    <div class="spinner sm spinner-primary mt-3"></div>
                  </div>
                </b-col>

            </b-card>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>
  </div>
</template>


<script>
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Edit Permissions"
  },
  data() {
    return {
      isLoading: true,
      SubmitProcessing:false,
      permissions: [],
      role: {
        name: "",
        description: ""
      }
    };
  },

  methods: {
    //------------- Submit Validation Update Permissions
    Submit_Permission() {
      this.$refs.Edit_Permission.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          this.Update_Permission();
        }
      });
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

    //------------------------ Update Permissions -------------------\\
    Update_Permission() {
       this.SubmitProcessing = true;
      NProgress.start();
      NProgress.set(0.1);
      let id = this.$route.params.id;
      axios
        .put(`roles/${id}`, {
          role: this.role,
          permissions: this.permissions
        })
        .then(response => {
          this.SubmitProcessing = false;
          NProgress.done();
          this.makeToast(
            "success",
            this.$t("Update.TitleRole"),
            this.$t("Success")
          );

          this.$router.push({ name: "groupPermission" });
          this.$store.dispatch("refreshUserPermissions");
        })
        .catch(error => {
          this.SubmitProcessing = false;
          NProgress.done();
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //---------------------------------------Get Elements Permission Edit ------------------------------\\
    GetElements() {
      let id = this.$route.params.id;
      axios
        .get(`roles/${id}/edit`)
        .then(response => {
          this.role = response.data.role;
          this.permissions = response.data.permissions;
          this.isLoading = false;
        })
        .catch(response => {
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    }
  }, //end Methods

  created: function() {
    this.GetElements();
  }
};
</script>