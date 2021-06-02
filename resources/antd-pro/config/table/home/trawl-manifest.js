import moment from "moment";

export default [
  {
    title: "No",
    dataIndex: "number",
    colspan: 1,
    classes: ["trawl-text-center"]
  },
  {
    title: "Nomor Manifest",
    colspan: 5,
    classes: ["trawl-text-center"],
    scopedSlots: { customRender: "manifest" }
  },
  {
    title: "Mitra Pengirim",
    colspan: 5,
    scopedSlots: { customRender: "origin_partner" }
  },
  {
    title: "Mitra Penerima",
    colspan: 5,
    scopedSlots: { customRender: "partner" }
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
