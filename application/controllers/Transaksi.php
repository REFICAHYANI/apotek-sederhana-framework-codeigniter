<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transaksi extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Transaksi_model');
    }

    public function index()
	{
        $data['title'] = 'Data transaksi';
        $transaksi = $this->Transaksi_model->get_all()->result();
        foreach ($transaksi as $key => $value) {
            $transaksi[$key]->obat = $this->Transaksi_model->get_obat($value->id)->result();
        }
        $data['transaksi'] = $transaksi;
        $data['main_view'] = 'transaksi/index';
		$this->load->view('template', $data);
    }
    
    public function tambah()
    {
        $data['title'] = 'Tambah transaksi';
        $data['main_view'] = 'transaksi/tambah';
        $this->form_validation->set_rules('nama_pembeli', 'Nama Pembeli', 'required|trim');
        $this->form_validation->set_rules('data_obat', 'Obat', 'required');
        if ($this->form_validation->run() == FALSE)
        {
            $this->load->model('Obat_model');
            $data['obat'] = $this->Obat_model->get_all();
            $this->load->view('template', $data);
        }
        else
        {
            $arrObat = json_decode($this->input->post('data_obat'));
            $data_transaksi = [
                'tgl' => date('Y-m-d h:i:s'),
                'nama_pembeli' => $this->input->post('nama_pembeli'),
                'admin_id' => $this->session->userdata('user_id'),
            ];
            $tambah = $this->Transaksi_model->create($data_transaksi);
            $transaksi_id = $this->db->insert_id();
            foreach ($arrObat as $ob) {
                $detail_transaksi = [
                    'transaksi_id' => $transaksi_id,
                    'kode_obat' => $ob->kode,
                    'jumlah' => $ob->jumlah,
                ];
                $this->Transaksi_model->create_detail($detail_transaksi);
            }
            $msg = $tambah ? 'Berhasil ditambah' : 'Gagal ditambah';
            $this->session->set_flashdata('info', $msg);
            redirect('transaksi');
        }
    }

    public function hapus($id = null)
    {
        if (! $id) return show_404();
        $this->db->delete('transaksi', ['id' => $id]);
        $this->session->set_flashdata('info', 'Berhasil dihapus');
        redirect('transaksi');
    }
}