import moment from "moment";

export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "ID Order",
    scopedSlots: { customRender: "id_order" },
    colspan: 5,
    classes: ["trawl-text-left"]
  },
  {
    title: "Customer",
    dataIndex: "sender_name",
    key: "sender_name",
    colspan: 4,
    scopedSlots: { customRender: "sender_name" }
  },
  {
    title: "Lokasi Pengiriman",
    key: "address",
    colspan: 5,
    scopedSlots: { customRender: "address" }
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
  // {
  //   title: "Nilai Barang",
  //   key: "package_item_price",
  //   dataIndex: "package_item_price",
  //   align: "center",
  //   scopedSlots: { customRender: "package_price" }
  // },
  {
    title: "Tanggal Order",
    dataIndex: "created_at",
    colspan: 4,
    classes: ["trawl-text-center"],
    customRender(text, row, index) {
      return moment(text).format("ddd, DD MMM YYYY HH:mm:ss");
    }
  }
];
