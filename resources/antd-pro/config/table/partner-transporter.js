export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "Jenis Kendaraan",
    dataIndex: "type",
    key: "type",
    scopedSlots: { customRender: "type" }
  },
  {
    title: "No Pol",
    dataIndex: "registration_number",
    key: "registration_number",
    scopedSlots: { customRender: "registration_number" }
  },
  {
    title: "Kapasitas (Kg)",
    dataIndex: "weight",
    key: "weight",
    scopedSlots: { customRender: "weight" }
  },
  {
    title: "Detail Kendaraan",
    key: "detail",
    scopedSlots: { customRender: "detail" }
  },
  {
    title: "Action",
    key: "action",
    align: "center",
    width: "100px",
    scopedSlots: { customRender: "action" }
  }
];
