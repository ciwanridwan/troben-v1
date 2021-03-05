export default [
  {
    title: "No",
    key: "number",
    dataIndex: "number",
    scopedSlots: { customRender: "number" }
  },
  {
    title: "No Pol",
    dataIndex: "registration_number",
    key: "registration_number",
    scopedSlots: { customRender: "registration_number" }
  },
  {
    title: "Jenis",
    dataIndex: "type.name",
    key: "type",
    scopedSlots: { customRender: "type" }
  },
  {
    title: "Kode Mitra",
    dataIndex: "partner.code",
    key: "code",
    scopedSlots: { customRender: "code" }
  },
  {
    title: "Tahun",
    dataIndex: "production_year",
    key: "production_year",
    scopedSlots: { customRender: "production_year" }
  },
  {
    title: "Dimensi",
    key: "dimension",
    scopedSlots: { customRender: "dimension" }
  },
  {
    title: "Nama STNK",
    dataIndex: "registration_name",
    key: "registration_name",
    scopedSlots: { customRender: "registration_name" }
  },
  {
    title: "Pajak STNK",
    dataIndex: "registration_year",
    key: "registration_year",
    scopedSlots: { customRender: "registration_year" }
  },
  {
    title: "Foto",
    key: "image",
    scopedSlots: { customRender: "image" }
  },
  {
    title: "Action",
    key: "action",
    align: "center",
    width: "100px",
    scopedSlots: { customRender: "action" }
  }
];
