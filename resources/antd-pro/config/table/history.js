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
    dataIndex: "customer",
    key: "customer",
    scopedSlots: { customRender: "customer" }
  },
  {
    title: "Tujuan",
    key: "destination",
    scopedSlots: { customRender: "destination" }
  },
  {
    title: "Harga / Kg",
    dataIndex: "price",
    key: "price",
    align: "center",
    scopedSlots: { customRender: "price" }
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
    dataIndex: "created_at",
    scopedSlots: { customRender: "created_at" }
  }
];
