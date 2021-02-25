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
    dataIndex: "type",
    key: "type",
    scopedSlots: { customRender: "type" }
  },
  {
    title: "Kode Mitra",
    key: "code",
    scopedSlots: { customRender: "code" }
  },
  {
    title: "Tahun",
    key: "year",
    scopedSlots: { customRender: "year" }
  },
  {
    title: "Dimensi",
    key: "dimension",
    scopedSlots: { customRender: "dimension" }
  },
  {
    title: "Nama STNK",
    key: "registration_name",
    scopedSlots: { customRender: "registration_name" }
  },
  {
    title: "Pajak STNK",
    key: "registration_tax_year",
    scopedSlots: { customRender: "registration_tax_year" }
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
