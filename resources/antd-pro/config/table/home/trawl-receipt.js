import moment from "moment";

export default [
  {
    title: "No",
    dataIndex: "number",
    colspan: 1,
    classes: ["trawl-text-center"]
  },
  {
    title: "Nomor Resi",
    dataIndex: "code.content",
    scopedSlots: { customRender: "id_order" },
    colspan: 5,
    classes: [""]
  },
  {
    title: "Mitra Penerima",
    colspan: 5,
    customRender: (text, row, index) => {
      return {
        children: row.deliveries[0]?.partner?.name
          ? row.deliveries[0].partner.name
          : ""
      };
    }
  },
  {
    title: "Tujuan",
    key: "address",
    dataIndex: "receiver_address",
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
