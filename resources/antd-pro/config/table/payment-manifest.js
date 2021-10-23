import moment from "moment";

export default [
  {
    title: "No",
    dataIndex: "number",
    colspan: 1,
    classes: ["trawl-text-center"]
  },
  {
    title: "Kode Mitra",
    colspan: 3,
    scopedSlots: { customRender: "driver" }
  },
  {
    title: "Nomor Manifest",
    colspan: 5,
    scopedSlots: { customRender: "manifest" }
  },
  {
    title: "Keterangan",
    key: "detail",
    colspan: 5,
    scopedSlots: { customRender: "detail" }
  },
  {
    title: "Pendapatan",
    key: "balance",
    scopedSlots: { customRender: "balance" },
    sorter: true,
    colspan: 5
  },
  {
    title: "Tanggal Order",
    dataIndex: "created_at",
    colspan: 5,
    classes: ["trawl-text-center"],
    customRender(text, row, index) {
      return moment(text).format("ddd, DD MMM YYYY HH:mm:ss");
    }
  }
];
