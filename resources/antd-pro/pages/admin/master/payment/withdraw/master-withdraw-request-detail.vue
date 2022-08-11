<template>
    <content-layout title="Detail Pencairan Mitra">
        <template slot="content">
            <a-card>
                <a-row type="flex" justify="space-between" :gutter="[64, 10]">
                    <a-col>
                        <h3 class="text-gray">Total</h3>
                        <h2 class="mb-0 title-price">
                            <template v-if="total != 0">
                                <b>Rp.{{ formatPrice(total) }}</b>
                            </template>
                            <template v-else>
                                <b>Rp. 0</b>
                            </template>
                        </h2>
                    </a-col>
                    <a-col>
                        <h3 class="text-gray">Total Unapproved</h3>
                        <h2 class="mb-0 title-price">
                            <template v-if="total_unapproved != 0">
                                <b>Rp.{{ formatPrice(total_unapproved) }}</b>
                            </template>
                            <template v-else>
                                <b>Rp. 0</b>
                            </template>
                        </h2>
                    </a-col>
                    <a-col>
                        <h3 class="text-gray">Total Approved</h3>
                        <h2 class="mb-0 title-price">
                            <template v-if="total_approved != 0">
                                <b>Rp.{{ formatPrice(total_approved) }}</b>
                            </template>
                            <template v-else>
                                <b>Rp. 0</b>
                            </template>
                        </h2>
                    </a-col>
                </a-row>
            </a-card>
            
            <a-card class="mt-2 mb-1">
                <a-row type="flex" justify="space-between">
                    <a-col :span="6">
                        <a-input-search v-model="filter.q" @change="searchData()" placeholder="Cari No Resi"></a-input-search>
                    </a-col>
                </a-row>
            </a-card>

            <a-table
                class="mb-table"
                :columns="requestColumns"
                :dataSource="lists"
                :loading="loading"
                :class="['trawl']">
                <span slot="code" slot-scope="record" class="fw-medium">{{ record.receipt }}</span>
                <span slot="total_payment" slot-scope="record">Rp. {{ formatPrice(record.total_payment) }}</span>
                <span slot="total_accepted" slot-scope="record">Rp. {{ formatPrice(record.commission_discount) }}</span>
                <span slot="approved" slot-scope="record">
                    <template v-if="record.approved == 'pending'">
                        <template v-if="approved_at">
                            <input type="checkbox" disabled>
                        </template>
                        <template v-else>
                            <input type="checkbox" v-model="receipt" :value="record">
                        </template>
                        <span class="text-gray">Pending</span>
                    </template>
                    <template v-else>
                        <span class="text-green fw-medium">
                            <a-icon type="check"/>
                            Success
                        </span>
                    </template>
                </span>
            </a-table>
        </template>

        <template slot="footer">
            <a-layout-footer :class="['trawl-content-footer']">
                <a-row type="flex" :gutter="24">
                    <a-col :span="14">
                        <template v-if="this.approved_at != null">
                            Approved At
                            <span class="fw-medium">
                                {{ moment(this.approved_at).format("ddd, DD MMM YYYY HH:mm:ss") }}
                            </span>
                        </template>
                    </a-col>
                    <a-col :span="4">
                        <a-button type="primary mr-1" @click="store()" :disabled="receipt.length == 0">Selesai</a-button>
                        <a :href="routeUri('admin.payment.withdraw.request')" class="ant-btn ant-btn-danger">Back</a>
                    </a-col>
                </a-row>
            </a-layout-footer>
        </template>
    </content-layout>
</template>
<script>

import requestColumns from "../../../../../config/table/withdraw/detail";
import ContentLayout from "../../../../../layouts/content-layout.vue";
import $ from 'jquery'

export default {
    components: {
        ContentLayout,
    },
    data: () => ({
        requestColumns,
        loading: false,
        id: window.location.href.split('/').pop(),
        lists: [],
        total: 0,
        total_unapproved: '',
        total_approved: '',
        receipt: [],
        approved_at: '',
        filter: {
            q: ''
        },
        data_filters: []
    }),
    created() {
        this.getDatas()
    },
    computed: {
    },
    methods: {
        getTotal() {
            let total = 0;
            let total_unapproved = 0;
            let total_approved = 0;
            this.lists.forEach(item => {
                total += Number(item.commission_discount);
                if(item.approved == 'pending'){
                    total_unapproved += Number(item.commission_discount);
                }
                if(item.approved == 'success'){
                    total_approved += Number(item.commission_discount);
                }
            });
            this.total = total
            this.total_unapproved = total_unapproved
            this.total_approved = total_approved
        },
        formatPrice(value) {
            let val = (value/1).toFixed(2).replace('.', ',')
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
        },
        /**URL getDatas -> admin.payment.withdraw.request.detail */
        getDatas(){
            this.loading = true
            let uri = this.routeUri(`admin.payment.withdraw.request.detailAjax`, {id: this.id})
            this.$http.get(uri)
            .then((res)=>{
                this.lists = res.data.data.rows
                this.approved_at = res.data.data.approved_at
                this.loading = false
                this.getTotal()
            }).catch(function (error) {
                console.error(error);
                this.loading = false
            });
        },
        /**URL store -> admin.payment.withdraw.request.approve */
        store(){
            var receipt = []
            this.receipt.forEach(item => {
                var object = item.receipt
                receipt.push(object)
            });

            let uri = this.routeUri("admin.payment.withdraw.request.approve")
            this.$http.post(uri, {
                id: this.id,
                receipt: receipt
            })
            .then((res)=>{
                console.log(res)
                this.getDatas()
                this.$message.success(`List approved`);
                this.approved_at = res.data.data.action_at
                this.receipt = []
            }).catch(function (error) {
                console.log(error)
            });
        },
        /**URL searchData -> admin.payment.withdraw.request.findByReceipt */
        searchData(){
            if(this.filter.q != ''){
                let uri = this.routeUri("admin.payment.withdraw.request.findByReceipt")
                this.$http.get(uri, {
                    params: {
                        receipt: this.filter.q
                    }
                })
                .then((res)=>{
                    this.lists = res.data.data
                }).catch(function (error) {
                    console.log(error)
                });
            }else{
                this.getDatas()
            }
        }
    },
};
</script>

<style type="scss">
    .ant-card-bordered{
        border: unset !important;
    }
    .ant-btn-primary{
        background: #3D8824;
        border-color: #3D8824;
    }
    .ant-btn-danger{
        background: #fff;
        border-color: #f5f5f5;
        color: #000;
    }
    .ant-btn-danger:hover{
        background-color: #fff;
        color: #000;
    }
    .mr-1{
        margin-right: 10px;
    }
    .fw-medium{
        font-weight: 600;
    }
    .mb-table{
        margin-bottom: 100px;
    }
    .text-green{
        color: #3D8824;
    }
    .text-gray{
        color: #bfbfbf;
    }
    .mt-2{
        margin-top: 20px;
    }
    .mb-1{
        margin-bottom: 10px;
    }
</style>
