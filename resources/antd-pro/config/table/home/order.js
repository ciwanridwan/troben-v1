export default [
  {
    title: "No",
    dataIndex: "id",
    customRender: (text, row, index) => {
      return {
        children: text,
        attrs: {
          rowSpan: 2
        }
      };
    }
  },
  {
    title: "ID Order",
    dataIndex: "barcode",
    key: "barcode"
  },
  {
    title: "Mitra Penerima",
    dataIndex: "receiver_name"
  },
  {
    title: "Lokasi Pengiriman",
    key: "address",
    scopedSlots: { customRender: "address" }
  },
  {
    title: "Order By",
    key: "order_by",
    scopedSlots: { customRender: "order_by" },
    sorter: true
  },
  {
    title: "Tanggal Order",
    dataIndex: "created_at"
  }
];
