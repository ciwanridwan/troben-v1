<template>
    <content-layout title="Detail Pencairan Mitra">
        <template slot="content">
            <a-card>
                <a-row type="flex" justify="space-between" :gutter="[64, 10]">
                    <a-col>
                        <h3 class="text-gray">Total</h3>
                        <h2 class="mb-0 title-price">
                            <b>Rp.{{ formatPrice(data_total.total_approved + data_total.total_unapproved) }}</b>
                        </h2>
                    </a-col>
                    <a-col>
                        <h3 class="text-gray">Total Unapproved</h3>
                        <h2 class="mb-0 title-price">
                            <template v-if="data_total.total_unapproved != 0">
                                <b>Rp.{{ formatPrice(data_total.total_unapproved) }}</b>
                            </template>
                            <template v-else>
                                <b>Rp. 0</b>
                            </template>
                        </h2>
                    </a-col>
                    <a-col>
                        <h3 class="text-gray">Total Approved</h3>
                        <h2 class="mb-0 title-price">
                            <template v-if="data_total.total_approved != 0">
                                <b>Rp.{{ formatPrice(data_total.total_approved) }}</b>
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
                    <a-col :span="8">
                        <a-input-search v-model="filter.q" @change="searchData()" placeholder="Cari No Resi"></a-input-search>
                    </a-col>
                    <a-col :span="5">
                        <template v-if="approved_at != null && transferred_at == null">
                            <a-button type="primary" class="w-100" @click="showModal()">
                                <a-icon type="upload"></a-icon>
                                Upload Bukti Transfer
                            </a-button>
                        </template>
                        <template v-else-if="transferred_at != null">
                            <a-button type="primary" class="w-100" @click="showModal()">
                                Lihat Bukti Transfer
                            </a-button>
                        </template>
                    </a-col>
                </a-row>
            </a-card>

            <a-modal
                v-model="visible"
                :width="450"
                @cancel="onCancel"
                :closable="true"
                :mask-closable="true"
                footer=""
            >
                <template slot="closeIcon"
                    ><a-icon type="close" @click="onCancel"></a-icon
                ></template>
                <template slot="title">
                    <div class="red-color">
                        <template v-if="transferred_at == null">
                            Upload Bukti Transfer
                        </template>
                        <template v-else>
                            Bukti Transfer
                        </template>
                    </div>
                </template>
                <a-row type="flex" :gutter="[24, 24]">
                    <a-col :span="24">
                        <template v-if="transferred_at == null">
                            <input type="file" name="file" @change="uploadFile" ref="file">
                            <div class="image-preview">
                                <center>
                                    <img class="preview" :src="imageData" v-if="imageData.length > 0">
                                    <img src="/assets/no-photo.png" class="preview" v-else>
                                </center>
                            </div>
                            <div class="mt-2">
                                <a-button type="primary" class="pr-2 pl-2" @click="submitFile">Submit</a-button>
                            </div>
                        </template>
                        <template v-else>
                            <div class="image-success-upload">
                                <center>
                                    <img :src="attachment_transfer" alt="">
                                </center>
                            </div>
                        </template>
                    </a-col>
                </a-row>
            </a-modal>

            <a-table
                class="mb-table"
                :columns="requestColumns"
                :dataSource="lists"
                :loading="loading"
                :class="['trawl']">
                <span slot="code" slot-scope="record" class="fw-medium">{{ record.receipt }}</span>
                <span slot="total_payment" slot-scope="record">Rp. {{ formatPrice(record.total_payment) }}</span>
                <span slot="total_accepted" slot-scope="record">Rp. {{ formatPrice(record.total_accepted) }}</span>
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
                        <template v-if="this.approved_at != null && transferred_at == null">
                            Approved At
                            <span class="fw-medium">
                                {{ moment(this.approved_at).format("ddd, DD MMM YYYY HH:mm:ss") }}
                            </span>
                        </template>
                        <template v-if="transferred_at != null">
                            Transferred At
                            <span class="fw-medium">
                                {{ moment(this.transferred_at).format("ddd, DD MMM YYYY HH:mm:ss") }}
                            </span>
                        </template>
                    </a-col>
                    <a-col :span="4">
                        <a-button type="primary mr-1" @click="store()" :disabled="receipt.length == 0">Cairkan</a-button>
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
        transferred_at: '',
        filter: {
            q: ''
        },
        data_filters: [],
        data_total: {},
        images: null,
        imageData: "",
        visible: false,
        attachment_transfer: ''
    }),
    created() {
        this.getDatas()
    },
    computed: {
    },
    methods: {
        onCancel() {
            this.visible = false;
        },
        showModal(key) {
            this.visible = true;
        },
        getTotal() {
            let total = 0;
            let total_unapproved = 0;
            let total_approved = 0;
            this.lists.forEach(item => {
                total += Number(item.total_accepted);
                if(item.approved == 'pending'){
                    total_unapproved += Number(item.total_accepted);
                }
                if(item.approved == 'success'){
                    total_approved += Number(item.total_accepted);
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
                this.data_total = res.data.data
                this.approved_at = res.data.data.approved_at
                this.transferred_at = res.data.data.transferred_at
                this.attachment_transfer = res.data.data.attachment_transfer
                this.loading = false
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
                let uri = this.routeUri(`admin.payment.withdraw.request.findByReceipt`, {id: this.id})
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
        },
        uploadFile(event) {
            this.images = this.$refs.file.files[0];

            var input = event.target;
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = (e) => {
                this.imageData = e.target.result;
            };
                reader.readAsDataURL(input.files[0]);
            }
        },
        submitFile() {
            const formData = new FormData();
            formData.append('attachment_transfer', this.images);

            let uri = this.routeUri(`admin.payment.withdraw.request.attachmentTransfer`, {id: this.id})
            
            this.$http.post(uri, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }).then((res)=>{
                this.$message.success(`Bukti transfer berhasil di simpan`);
                this.getDatas()
                this.visible = false;
            })
            .catch(function(error){
                console.log(error)
            });
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
    .w-100{
        width: 100% !important;
    }
    .ant-modal-mask{
        opacity: 0.4;
    }
    .image-preview{
        border: 2px solid #e8e8e8;
        border-radius: 10px;
        padding: 10px;
        margin-top: 30px;
    }
    .image-success-upload{
        border: 2px solid #e8e8e8;
        border-radius: 10px;
        padding: 10px;
    }
    .image-preview img{
        width: 100%;
    }
    .pr-2{
        padding-right: 30px;
    }
    .pl-2{
        padding-left: 23px;
    }
</style>
