export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "Nama Alat",
    dataIndex: "name",
    key: "name",
    scopedSlots: { customRender: "name" }
  },
  {
    title: "Kapasitas Berat (Kg)",
    dataIndex: "capacity",
    key: "capacity",
    scopedSlots: { customRender: "capacity" }
  },
  {
    title: "Kapasitas Tinggi (cm)",
    dataIndex: "height",
    key: "height",
    scopedSlots: { customRender: "height" }
  },
  {
    title: "Jumlah",
    dataIndex: "count",
    key: "count",
    scopedSlots: { customRender: "count" }
  },

  {
    title: "Action",
    key: "action",
    align: "center",
    width: "100px",
    scopedSlots: { customRender: "action" }
  }
];
