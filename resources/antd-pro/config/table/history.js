import moment from "moment";

export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "Id Order",
    key: "order",
    dataIndex: "barcode",
    scopedSlots: { customRender: "order" }
  },
  {
    title: "Customer",
    dataIndex: "customer.name",
    key: "customer",
    scopedSlots: { customRender: "customer" }
  },
  {
    title: "Tujuan",
    dataIndex: "receiver_address",
    key: "destination",
    scopedSlots: { customRender: "destination" }
  },
  {
    title: "Harga / Kg",
    dataIndex: "total_amount",
    key: "total_amount",
    align: "center",
    scopedSlots: { customRender: "total_amount" }
  },
  {
    title: "Total",
    key: "payment",
    dataIndex: "total_amount",
    align: "center",
    scopedSlots: { customRender: "payment" }
  },
  {
    title: "Nilai Barang",
    key: "package_item_price",
    dataIndex: "package_item_price",
    align: "center",
    scopedSlots: { customRender: "package_price" }
  },
  {
    title: "Tanggal",
    key: "created_at",
    // dataIndex: "created_at",
    customRender: (text, row, index) => {
      return {
        attrs: {
          colSpan: 2
        },
        children: moment(text).format("ddd, DD MMM YYYY HH:mm:ss")
      };
    }
  }
];
