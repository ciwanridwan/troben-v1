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
    title: "Capacity",
    key: "capacity",
    scopedSlots: { customRender: "capacity" }
  },
  {
    title: "Action",
    key: "action",
    align: "center",
    width: "100px",
    scopedSlots: { customRender: "action" }
  }
];
