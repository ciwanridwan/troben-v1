import moment from "moment";

export default [
  {
    title: "No",
    dataIndex: "number"
  },
  {
    title: "ID Order",
    dataIndex: "code.content"
  },
  {
    title: "Mitra Penerima",
    customRender: (text, row, index) => {
      return {
        children: row.deliveries[0]?.partner.name
      };
    }
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
    dataIndex: "created_at",
    customRender(text) {
      return moment(text).format("ddd, DD MMM YYYY HH:mm:ss");
    }
  }
];
