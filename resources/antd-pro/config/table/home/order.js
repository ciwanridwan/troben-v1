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
    dataIndex: "sender_address"
  },
  {
    title: "Order By",
    dataIndex: "order_by",
    sorter: true
  },
  {
    title: "Tanggal Order",
    dataIndex: "created_at"
  }
];
