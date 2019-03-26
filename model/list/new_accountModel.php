<?php

class new_accountModel extends Model
{
    /**
     * 数据表名
     * @var string
     */
    protected $logTable = 'new_account_log';
    protected $userId = 0;//用户id
    protected $totalMoney = 0.00;//总金额=可用+冻结+代收
    protected $useMoney = 0.00;//可用金额=充值+回款
    protected $rechargeMoney = 0.00;//充值金额
    protected $repaymentMoney = 0.00;//还款金额
    protected $frozenMoney = 0.00;//冻结金额
    protected $collectionMoney = 0.00;//待收金额

    /**
     * 添加帐户信息
     * @param int $user_id
     * @param int $total_money 总金额
     * @param int $use_money 可用金额
     * @param int $recharge_money 充值金额
     * @param int $repayment_money 回款金额
     * @param int $frozen_money 冻结金额
     * @param int $collection_money 代收金额
     * @return array
     */
    public function addAccountRecord($user_id, $total_money = 0, $use_money = 0, $recharge_money = 0, $repayment_money = 0, $frozen_money = 0, $collection_money = 0)
    {
        $insert = array(
            'user_id'          => $user_id,
            'total_money'      => $total_money,
            'use_money'        => $use_money,
            'recharge_money'   => $recharge_money,
            'repayment_money'  => $repayment_money,
            'frozen_money'     => $frozen_money,
            'collection_money' => $collection_money,
        );
        $this->insert($insert);
        return $this->get_one(['user_id' => $user_id]);
    }

  /**
     * 资金操作日志
     * @param int $user_id 用户ID
     * @param string $in_or_out 转出还是转入
     * @param string $in_or_out_use 转出还是转入
     * @param string $type 操作类型
     * @param float $money 操作金额
     * @param float $total_money 总金额
     * @param float $use_money 可用金额
     * @param float $recharge_money 充值金额
     * @param float $repayment_money 回款金额
     * @param float $frozen_money 冻结金额
     * @param float $collection_money 代收金额
     * @param string $remark 备注
     * @param string $do_remark 操作备注
     * @param string $create_ip 添加IP
     * @param string $order_id 操作订单号
     * @return string|bool
     */
    public function addLog($user_id, $in_or_out, $in_or_out_use, $type, $money, $total_money, $use_money, $recharge_money, $repayment_money,
                           $frozen_money, $collection_money, $remark, $do_remark, $create_ip = '', $order_id)
    {
        if (!$order_id) {
            return "订单号错误";
        }
        $insert = array(
            'user_id'          => $user_id,
            'in_or_out'        => $in_or_out,
            'in_or_out_use'    => $in_or_out_use,
            'order_id'         => $order_id,
            'type'             => $type,
            'money'            => $money,
            'total_money'      => $total_money,
            'use_money'        => $use_money,
            'recharge_money'   => $recharge_money,
            'repayment_money'  => $repayment_money,
            'frozen_money'     => $frozen_money,
            'collection_money' => $collection_money,
            'remark'           => $remark,
            'do_remark'        => $do_remark,
            'add_ip'           => $create_ip ? $create_ip : getIp(),
            'add_time'         => getRequestTime()
        );
        if ($money < 0 || $total_money < 0 || $use_money < 0 || $recharge_money < 0 || $repayment_money < 0 || $frozen_money < 0 || $collection_money < 0) {
            return '资金有误！';
        }
        $log_model = M($this->logTable);
        if ($log_model->insert($insert)) {
            return true;
        } else {
            return '资金操作日志写入失败！';
        }
    }
    
    /**
     * 充值金额【充值】
     * @param int $user_id 用户ID
     * @param float $recharge_money 充值金额
     * @param string $type 添加类型
     * @param string $remark 添加备注
     * @param string $create_ip 由于是远程curl调用，所以ip必须要传递
     * @param string $order_id 操作订单号
     * @return string|bool
     */
    public function rechargeMoney($user_id, $recharge_money, $type, $remark, $create_ip = '', $order_id)
    {
        if ($recharge_money <= 0) {
            return '添加资金出错！';
        }
        $accountInfo = $this->get_one(['user_id' => $user_id]);
        if ($accountInfo == false) {
            $accountInfo = $this->addAccountRecord($user_id);
            if ($accountInfo == false) {
                return "添加账户出错";
            }
        }
        if (!$this->matchAccount($user_id)) {
            return "资金有误，请联系客服！";
        }
        $update = array(
            'total_money'    => $accountInfo['total_money'] + $recharge_money,
            'use_money'      => $accountInfo['use_money'] + $recharge_money,
            'recharge_money' => $accountInfo['recharge_money'] + $recharge_money
        );
        $where = array(
            'user_id'        => $user_id,
            'total_money'    => $accountInfo['total_money'],
            'use_money'      => $accountInfo['use_money'],
            'recharge_money' => $accountInfo['recharge_money']
        );
        if (!$this->update($update, $where)) {
            return '添加资金出错！';
        }
        $do_remark = "total_money+{$recharge_money},use_money+{$recharge_money},recharge_money+{$recharge_money}";
        return $this->addLog($user_id, "转入", "转入", $type, $recharge_money, $accountInfo['total_money'] + $recharge_money,
            $accountInfo['use_money'] + $recharge_money, $accountInfo['recharge_money'] + $recharge_money,
            $accountInfo['repayment_money'], $accountInfo['frozen_money'], $accountInfo['collection_money'],
            $remark, $do_remark, $create_ip, $order_id);
    }

    /**
     * 充值金额【充值回款金额，元金币收益，元金币本金，体验金，融资借款】
     * @param int $user_id 用户ID
     * @param float $repayment_money 充值回款金额
     * @param string $type 添加类型
     * @param string $remark 添加备注
     * @param string $create_ip 由于是远程curl调用，所以ip必须要传递
     * @param string $order_id 操作订单号
     * @return string|bool
     */
    public function rechargeRepaymentMoney($user_id, $repayment_money, $type, $remark, $create_ip = '', $order_id)
    {
        if ($repayment_money <= 0) {
            return '添加资金出错！';
        }
        $accountInfo = $this->get_one(['user_id' => $user_id]);
        if ($accountInfo == false) {
            $accountInfo = $this->addAccountRecord($user_id);
            if ($accountInfo == false) {
                return "添加账户出错";
            }
        }
        if (!$this->matchAccount($user_id)) {
            return "资金有误，请联系客服！";
        }
        $update = array(
            'total_money'     => $accountInfo['total_money'] + $repayment_money,
            'use_money'       => $accountInfo['use_money'] + $repayment_money,
            'repayment_money' => $accountInfo['repayment_money'] + $repayment_money
        );
        $where = array(
            'user_id'         => $user_id,
            'total_money'     => $accountInfo['total_money'],
            'use_money'       => $accountInfo['use_money'],
            'repayment_money' => $accountInfo['repayment_money']
        );
        if (!$this->update($update, $where)) {
            return '添加资金出错！';
        }
        $do_remark = "total_money+{$repayment_money},use_money+{$repayment_money},repayment_money+{$repayment_money}";
        return $this->addLog($user_id, "转入", "转入", $type, $repayment_money, $accountInfo['total_money'] + $repayment_money,
            $accountInfo['use_money'] + $repayment_money, $accountInfo['recharge_money'],
            $accountInfo['repayment_money'] + $repayment_money, $accountInfo['frozen_money'], $accountInfo['collection_money'],
            $remark, $do_remark, $create_ip, $order_id);
    }

    /**
     * 扣除金额【融资还款，提现扣除金额等】
     * @param int $user_id 用户ID
     * @param float $recharge_money 扣除充值金额
     * @param float $repayment_money 扣除回款金额
     * @param string $type 添加类型
     * @param string $remark 添加备注
     * @param string $create_ip 由于是远程curl调用，所以ip必须要传递
     * @param string $order_id 操作订单号
     * @return string|bool
     */
    public function deductMoney($user_id, $recharge_money, $repayment_money, $type, $remark, $create_ip = '', $order_id)
    {
        $money = $recharge_money + $repayment_money;
        if ($recharge_money < 0 || $repayment_money < 0 || $money <= 0) {
            return '扣除资金出错！';
        }
        $accountInfo = $this->get_one(['user_id' => $user_id]);
        if ($accountInfo == false) {
            $accountInfo = $this->addAccountRecord($user_id);
            if ($accountInfo == false) {
                return "添加账户出错";
            }
        }
        if (!$this->matchAccount($user_id)) {
            return "资金有误，请联系客服！";
        }
        if (bccomp($accountInfo['use_money'], $money, 4) == -1) {
            return '可用资金不足！余额为:' . $accountInfo['use_money'];
        }
        $update = array(
            'total_money'     => $accountInfo['total_money'] - $money,
            'use_money'       => $accountInfo['use_money'] - $money,
            'recharge_money'  => $accountInfo['recharge_money'] - $recharge_money,
            'repayment_money' => $accountInfo['repayment_money'] - $repayment_money,
        );
        $where = array(
            'user_id'         => $user_id,
            'total_money'     => $accountInfo['total_money'],
            'use_money'       => $accountInfo['use_money'],
            'recharge_money'  => $accountInfo['recharge_money'],
            'repayment_money' => $accountInfo['repayment_money'],
        );
        if (!$this->update($update, $where)) {
            return '扣除资金出错！';
        }
        $do_remark = "total_money-{$money},use_money-{$money},recharge_money-{$recharge_money},repayment_money-{$repayment_money}";
        return $this->addLog($user_id, "转出", "转出", $type, $money, round($accountInfo['total_money'] - $money, 4),
            round($accountInfo['use_money'] - $money, 4), round($accountInfo['recharge_money'] - $recharge_money, 4),
            round($accountInfo['repayment_money'] - $repayment_money, 4), $accountInfo['frozen_money'], $accountInfo['collection_money'],
            $remark, $do_remark, $create_ip, $order_id);
    }

    /**
     * 冻结可用金额【提现冻结，投资冻结】使用本方法，可以先调用一下whichMoney方法
     * @param int $user_id 用户ID
     * @param float $recharge_money 冻结充值金额
     * @param float $repayment_money 冻结回款金额
     * @param string $type 添加类型
     * @param string $remark 添加备注
     * @param string $create_ip 由于是远程curl调用，所以ip必须要传递
     * @param string $order_id 操作订单号
     * @return string|bool
     */
    public function lockMoney($user_id, $recharge_money, $repayment_money, $type, $remark, $create_ip = '', $order_id)
    {
        $money = $recharge_money + $repayment_money;
        if ($recharge_money < 0 || $repayment_money < 0 || $money <= 0) {
            return '冻结资金出错！';
        }
        $accountInfo = $this->get_one(['user_id' => $user_id]);
        if ($accountInfo == false) {
            $accountInfo = $this->addAccountRecord($user_id);
            if ($accountInfo == false) {
                return "添加账户出错";
            }
        }
        if (!$this->matchAccount($user_id)) {
            return "资金有误，请联系客服！";
        }
        if (bccomp($accountInfo['use_money'], $money, 4) == -1) {
            return '可用资金不足！余额为:' . $accountInfo['use_money'];
        }

        $update = array(
            'use_money'       => $accountInfo['use_money'] - $money,
            'recharge_money'  => $accountInfo['recharge_money'] - $recharge_money,
            'repayment_money' => $accountInfo['repayment_money'] - $repayment_money,
            'frozen_money'    => $accountInfo['frozen_money'] + $money,
        );
        $where = array(
            'user_id'         => $user_id,
            'use_money'       => $accountInfo['use_money'],
            'recharge_money'  => $accountInfo['recharge_money'],
            'repayment_money' => $accountInfo['repayment_money'],
            'frozen_money'    => $accountInfo['frozen_money'],
        );
        if (!$this->update($update, $where)) {
            return '添加资金出错！';
        }
        $do_remark = "use_money-{$money},recharge_money-{$recharge_money},repayment_money-{$repayment_money},frozen_money+{$money}";
        return $this->addLog($user_id, "不变", "转出", $type, $money, $accountInfo['total_money'],
            round($accountInfo['use_money'] - $money, 4), round($accountInfo['recharge_money'] - $recharge_money, 4),
            round($accountInfo['repayment_money'] - $repayment_money, 4), $accountInfo['frozen_money'] + $money,
            $accountInfo['collection_money'], $remark, $do_remark, $create_ip, $order_id);
    }

    /**
     * 冻结可用金额【提现冻结，投资冻结】
     * @param int $user_id 用户ID
     * @param float $money 冻结金额
     * @param int $do_type 动作类型【1先冻结充值，再冻结还款   2先冻结还款，再冻结充值】
     * @return array|string
     */
    public function whichMoney($user_id, $money, $do_type = 1)
    {
        if ($money <= 0) {
            return '冻结资金出错！';
        }
        $accountInfo = $this->get_one(['user_id' => $user_id]);
        if ($accountInfo == false) {
            $accountInfo = $this->addAccountRecord($user_id);
            if ($accountInfo == false) {
                return "添加账户出错";
            }
        }
        if (!$this->matchAccount($user_id)) {
            return "资金有误，请联系客服！";
        }
        if (bccomp($accountInfo['use_money'], $money) == -1) {
            return '可用资金不足！余额为:' . $accountInfo['use_money'];
        }
        $recharge_money = 0;
        $repayment_money = 0;
        if ($do_type == 1) {
            if ($accountInfo['recharge_money'] <= $money) {
                $recharge_money = $accountInfo['recharge_money'];
                $repayment_money = $money - $recharge_money;
            } else {
                $recharge_money = $money;
            }
        } else {
            if ($accountInfo['repayment_money'] <= $money) {
                $repayment_money = $accountInfo['repayment_money'];
                $recharge_money = $money - $repayment_money;
            } else {
                $repayment_money = $money;
            }
        }
        if ($recharge_money < 0) {
            $recharge_money = 0;
        }
        if ($repayment_money < 0) {
            $repayment_money = 0;
        }
        $recharge_money = (ceil(((int)($recharge_money * 1000)) / 10)) / 100;
        $repayment_money = (ceil(((int)($repayment_money * 1000)) / 10)) / 100;
        if (bccomp($recharge_money + $repayment_money, $money, 4) != 0) {
            return "计算规则有误，请联系客服！";
        }
        return ['recharge_money' => $recharge_money, 'repayment_money' => $repayment_money];
    }

    /**
     * 冻结扣除【提现】
     * @param int $user_id 用户ID
     * @param float $money 扣除金额
     * @param string $type 添加类型
     * @param string $remark 添加备注
     * @param string $create_ip 由于是远程curl调用，所以ip必须要传递
     * @param string $order_id 操作订单号
     * @return string|bool
     */
    public function subLockMoney($user_id, $money, $type, $remark, $create_ip = '', $order_id)
    {
        if ($money <= 0) {
            return '扣除冻结资金出错！';
        }
        $accountInfo = $this->get_one(['user_id' => $user_id]);
        if ($accountInfo == false) {
            $accountInfo = $this->addAccountRecord($user_id);
            if ($accountInfo == false) {
                return "添加账户出错";
            }
        }
        if (!$this->matchAccount($user_id)) {
            return "资金有误，请联系客服！";
        }
        if (bccomp($accountInfo['frozen_money'], $money, 4) == -1) {
            return '冻结资金不足！';
        }
        $update = array(
            'total_money'  => $accountInfo['total_money'] - $money,
            'frozen_money' => $accountInfo['frozen_money'] - $money,
        );
        $where = array(
            'user_id'      => $user_id,
            'total_money'  => $accountInfo['total_money'],
            'frozen_money' => $accountInfo['frozen_money'],
        );
        if (!$this->update($update, $where)) {
            return '扣除冻结资金出错！';
        }
        $do_remark = "total_money-{$money},frozen_money-{$money}";
        return $this->addLog($user_id, "转出", "不变", $type, $money, round($accountInfo['total_money'] - $money, 4),
            $accountInfo['use_money'], $accountInfo['recharge_money'],
            $accountInfo['repayment_money'], round($accountInfo['frozen_money'] - $money, 4),
            $accountInfo['collection_money'], $remark, $do_remark, $create_ip, $order_id);
    }

    /**
     * 提现扣除充值和回款【提现】
     * @param int $user_id 用户ID
     * @param float $recharge_money 充值金额
     * @param float $repayment_money 回款金额
     * @param string $type 添加类型
     * @param string $remark 添加备注
     * @param string $create_ip 由于是远程curl调用，所以ip必须要传递
     * @param string $order_id 操作订单号
     * @return string|bool
     */
    public function cashMoney($user_id, $recharge_money, $repayment_money, $type, $remark, $create_ip = '', $order_id)
    {
        $money = $recharge_money + $repayment_money;
        if ($recharge_money < 0 || $repayment_money < 0 || $money <= 0) {
            return '提现资金出错！';
        }
        $accountInfo = $this->get_one(['user_id' => $user_id]);
        if ($accountInfo == false) {
            $accountInfo = $this->addAccountRecord($user_id);
            if ($accountInfo == false) {
                return "添加账户出错";
            }
        }
        if (!$this->matchAccount($user_id)) {
            return "资金有误，请联系客服！";
        }
        if (bccomp($accountInfo['use_money'], $money, 4) == -1) {
            return '可用资金不足！余额为:' . $accountInfo['use_money'];
        }

        $update = array(
            'total_money'     => $accountInfo['total_money'] - $money,
            'use_money'       => $accountInfo['use_money'] - $money,
            'recharge_money'  => $accountInfo['recharge_money'] - $recharge_money,
            'repayment_money' => $accountInfo['repayment_money'] - $repayment_money,
        );
        $where = array(
            'user_id'         => $user_id,
            'total_money'     => $accountInfo['total_money'],
            'use_money'       => $accountInfo['use_money'],
            'recharge_money'  => $accountInfo['recharge_money'],
            'repayment_money' => $accountInfo['repayment_money'],
        );
        if (!$this->update($update, $where)) {
            return '提现资金出错！';
        }
        $do_remark = "total_money-{$money},use_money-{$money},recharge_money-{$recharge_money},repayment_money-{$repayment_money}";
        return $this->addLog($user_id, "转出", "转出", $type, $money, round($accountInfo['total_money'] - $money, 4),
            round($accountInfo['use_money'] - $money, 4), round($accountInfo['recharge_money'] - $recharge_money, 4),
            round($accountInfo['repayment_money'] - $repayment_money, 4), $accountInfo['frozen_money'],
            $accountInfo['collection_money'], $remark, $do_remark, $create_ip, $order_id);
    }

    /**
     * 使用冻结【放款冻结转代收（本金）】
     * @param int $user_id 用户ID
     * @param float $money 使用金额
     * @param string $type 添加类型
     * @param string $remark 添加备注
     * @param string $create_ip 由于是远程curl调用，所以ip必须要传递
     * @param string $order_id 操作订单号
     * @return string|bool
     */
    public function useLockMoney($user_id, $money, $type, $remark, $create_ip = '', $order_id)
    {
        if ($money <= 0) {
            return '使用冻结资金出错！';
        }
        $accountInfo = $this->get_one(['user_id' => $user_id]);
        if ($accountInfo == false) {
            $accountInfo = $this->addAccountRecord($user_id);
            if ($accountInfo == false) {
                return "添加账户出错";
            }
        }
        if (!$this->matchAccount($user_id)) {
            return "资金有误，请联系客服！";
        }
        if (bccomp($accountInfo['frozen_money'], $money, 4) == -1) {
            return '冻结资金不足！';
        }
        $update = array(
            'frozen_money'     => $accountInfo['frozen_money'] - $money,
            'collection_money' => $accountInfo['collection_money'] + $money,
        );
        $where = array(
            'user_id'          => $user_id,
            'frozen_money'     => $accountInfo['frozen_money'],
            'collection_money' => $accountInfo['collection_money'],
        );
        if (!$this->update($update, $where)) {
            return '使用冻结资金出错！';
        }
        $do_remark = "frozen_money-{$money},collection_money+{$money}";
        return $this->addLog($user_id, "不变", "不变", $type, $money, $accountInfo['total_money'],
            $accountInfo['use_money'], $accountInfo['recharge_money'],
            $accountInfo['repayment_money'], round($accountInfo['frozen_money'] - $money, 4),
            $accountInfo['collection_money'] + $money, $remark, $do_remark, $create_ip, $order_id);
    }

    /**
     * 添加代收【代收收益】
     * @param int $user_id 用户ID
     * @param float $money 添加代收金额
     * @param string $type 添加类型
     * @param string $remark 添加备注
     * @param string $create_ip 由于是远程curl调用，所以ip必须要传递
     * @param string $order_id 操作订单号
     * @return string|bool
     */
    public function addCollectionMoney($user_id, $money, $type, $remark, $create_ip = '', $order_id)
    {
        if ($money <= 0) {
            return '代收资金出错！';
        }
        $accountInfo = $this->get_one(['user_id' => $user_id]);
        if ($accountInfo == false) {
            $accountInfo = $this->addAccountRecord($user_id);
            if ($accountInfo == false) {
                return "添加账户出错";
            }
        }
        if (!$this->matchAccount($user_id)) {
            return "资金有误，请联系客服！";
        }
        $update = array(
            'total_money'      => $accountInfo['total_money'] + $money,
            'collection_money' => $accountInfo['collection_money'] + $money,
        );
        $where = array(
            'user_id'          => $user_id,
            'total_money'      => $accountInfo['total_money'],
            'collection_money' => $accountInfo['collection_money'],
        );
        if (!$this->update($update, $where)) {
            return '代收资金出错！';
        }
        $do_remark = "total_money+{$money},collection_money+{$money}";
        return $this->addLog($user_id, "转入", "不变", $type, $money, $accountInfo['total_money'] + $money,
            $accountInfo['use_money'], $accountInfo['recharge_money'],
            $accountInfo['repayment_money'], $accountInfo['frozen_money'],
            $accountInfo['collection_money'] + $money, $remark, $do_remark, $create_ip, $order_id);
    }
    
    /**
     * 减除代收【代收收益】
     * @param int $user_id 用户ID
     * @param float $money 减除代收金额
     * @param string $type 减除类型
     * @param string $remark 添加备注
     * @param string $create_ip 由于是远程curl调用，所以ip必须要传递
     * @param string $order_id 操作订单号
     * @return string|bool
     */
    public function lessCollectionMoney($user_id, $money, $type, $remark, $create_ip = '', $order_id)
    {
    	$accountInfo = $this->get_one(['user_id' => $user_id]);
    	if ($accountInfo == false) {
    		$accountInfo = $this->addAccountRecord($user_id);
    		if ($accountInfo == false) {
    			return "添加账户出错";
    		}
    	}
    	if (!$this->matchAccount($user_id)) {
    		return "资金有误，请联系客服！";
    	}
    	$update = array(
    			'total_money'      => $accountInfo['total_money'] - $money,
    			'collection_money' => $accountInfo['collection_money'] - $money,
    	);
    	$where = array(
    			'user_id'          => $user_id,
    			'total_money'      => $accountInfo['total_money'],
    			'collection_money' => $accountInfo['collection_money'],
    	);
    	if (!$this->update($update, $where)) {
    		return '代收资金出错！';
    	}

    	$do_remark = "total_money-{$money},collection_money-{$money}";
    	return $this->addLog($user_id, "转出", "不变", $type, $money, round($accountInfo['total_money'] - $money, 4),
    			$accountInfo['use_money'], $accountInfo['recharge_money'],
    			$accountInfo['repayment_money'], $accountInfo['frozen_money'],
    			round($accountInfo['collection_money'] - $money, 4), $remark, $do_remark, $create_ip, $order_id);
    }

    /**
     * 代收转回款【收益还款】
     * @param int $user_id 用户ID
     * @param float $money 回款金额
     * @param string $type 添加类型
     * @param string $remark 添加备注
     * @param string $create_ip 由于是远程curl调用，所以ip必须要传递
     * @param string $order_id 操作订单号
     * @return string|bool
     */
    public function repaymentMoney($user_id, $money, $type, $remark, $create_ip = '', $order_id)
    {
        if ($money <= 0) {
            return '回款资金出错！';
        }
        $accountInfo = $this->get_one(['user_id' => $user_id]);
        if ($accountInfo == false) {
            $accountInfo = $this->addAccountRecord($user_id);
            if ($accountInfo == false) {
                return "添加账户出错";
            }
        }
        if (!$this->matchAccount($user_id)) {
            return "资金有误，请联系客服！";
        }
        $update = array(
            'use_money'        => $accountInfo['use_money'] + $money,
            'repayment_money'  => $accountInfo['repayment_money'] + $money,
            'collection_money' => $accountInfo['collection_money'] - $money,
        );
        $where = array(
            'user_id'          => $user_id,
            'use_money'        => $accountInfo['use_money'],
            'repayment_money'  => $accountInfo['repayment_money'],
            'collection_money' => $accountInfo['collection_money'],
        );
        if (!$this->update($update, $where)) {
            return '回款资金出错！';
        }
        $do_remark = "use_money+{$money},repayment_money+{$money},collection_money-{$money}";
        return $this->addLog($user_id, "不变", "转入", $type, $money, $accountInfo['total_money'],
            $accountInfo['use_money'] + $money, $accountInfo['recharge_money'],
            $accountInfo['repayment_money'] + $money, $accountInfo['frozen_money'],
            round($accountInfo['collection_money'] - $money, 4), $remark, $do_remark, $create_ip, $order_id);
    }

    /**
     * 解除冻结资金【提现审核拒绝，投资流标】
     * @param int $user_id 用户ID
     * @param float $recharge_money 充值金额
     * @param float $repayment_money 回款金额
     * @param float $frozen_money 解冻金额
     * @param string $type 添加类型
     * @param string $remark 添加备注
     * @param string $create_ip 由于是远程curl调用，所以ip必须要传递
     * @param string $order_id 操作订单号
     * @return string|bool
     */
    public function unLockMoney($user_id, $recharge_money, $repayment_money, $frozen_money, $type, $remark, $create_ip = '', $order_id)
    {
        if ($recharge_money < 0 || $repayment_money < 0 || $frozen_money <= 0) {
            return '解冻资金出错！';
        }
        if (bccomp($frozen_money, ($recharge_money + $repayment_money), 4) != 0) {
            return '解冻资金出错！';
        }
        $accountInfo = $this->get_one(['user_id' => $user_id]);
        if ($accountInfo == false) {
            $accountInfo = $this->addAccountRecord($user_id);
            if ($accountInfo == false) {
                return "添加账户出错";
            }
        }
        if (!$this->matchAccount($user_id)) {
            return "资金有误，请联系客服！";
        }
        $update = array(
            'use_money'       => $accountInfo['use_money'] + $frozen_money,
            'recharge_money'  => $accountInfo['recharge_money'] + $recharge_money,
            'repayment_money' => $accountInfo['repayment_money'] + $repayment_money,
            'frozen_money'    => $accountInfo['frozen_money'] - $frozen_money,
        );
        $where = array(
            'user_id'         => $user_id,
            'use_money'       => $accountInfo['use_money'],
            'recharge_money'  => $accountInfo['recharge_money'],
            'repayment_money' => $accountInfo['repayment_money'],
            'frozen_money'    => $accountInfo['frozen_money'],
        );
        if (!$this->update($update, $where)) {
            return '解冻资金出错！';
        }
        $do_remark = "use_money+{$frozen_money},recharge_money+{$recharge_money},repayment_money+{$repayment_money},frozen_money-{$frozen_money}";
        return $this->addLog($user_id, "不变", "转入", $type, $frozen_money, $accountInfo['total_money'],
            $accountInfo['use_money'] + $frozen_money, $accountInfo['recharge_money'] + $recharge_money,
            $accountInfo['repayment_money'] + $repayment_money, round($accountInfo['frozen_money'] - $frozen_money, 4),
            $accountInfo['collection_money'], $remark, $do_remark, $create_ip, $order_id);
    }

    /**
     * 匹对用户资金与最后一笔日志，如果资金有误，就执行失败（每次进行资金操作都要匹配资金）
     * @param int $user_id 用户ID
     * @return bool
     */
    private function matchAccount($user_id)
    {
        $account_info = $this->get_one(['user_id' => $user_id]);
        $account_last_log = M($this->logTable)->get_one(['user_id' => $user_id], 'id desc');
        if (!$account_info) {
            return false;
        }
        if (!$account_last_log) {
            $account_last_log = [
                'total_money'      => 0,
                'use_money'        => 0,
                'recharge_money'   => 0,
                'repayment_money'  => 0,
                'frozen_money'     => 0,
                'collection_money' => 0
            ];
        }
        if (bccomp($account_info['total_money'], $account_last_log['total_money'], 4) != 0
            || bccomp($account_info['use_money'], $account_last_log['use_money'], 4) != 0
            || bccomp($account_info['recharge_money'], $account_last_log['recharge_money'], 4) != 0
            || bccomp($account_info['repayment_money'], $account_last_log['repayment_money'], 4) != 0
            || bccomp($account_info['frozen_money'], $account_last_log['frozen_money'], 4) != 0
            || bccomp($account_info['collection_money'], $account_last_log['collection_money'], 4) != 0
        ) {
            return false;
        }
        return true;
    }

    /**
     * 资金预警机制，查询来源
     * @param int $user_id 用户ID
     * @return bool
     */
    public function autoWaring($user_id)
    {

        $get_account_logs = M($this->logTable)->get_list(['user_id' => $user_id], 'id desc', 0, 20, 'order_id', 'type,order_id');
        if ($get_account_logs) {
            //获取充值记录
            $get_recharge_list = M("recharge")->get_list(['user_id' => $user_id, 'status' => '成功'], 'id desc', 0, 20, '', '`order`');
            $new_recharge_list = [];
            foreach ($get_recharge_list as $value) {
                if (!$value['order']) {
                    continue;
                }
                $new_recharge_list[$value['order']] = 1;
            }
            //获取投资、放款记录
            $get_bi_list = M("borrow_invest")->get_list(['invest_user_id' => $user_id, ['and', 'status', 'in', ['成功', '已放款', '已还款']]],
                'id desc', 0, 50, '', 'invest_order_id,sub_order_id');
            $new_invest_list = [];
            $new_sub_list = [];
            foreach ($get_bi_list as $value) {
                if (!$value['invest_order_id']) {
                    continue;
                }
                $new_invest_list[$value['invest_order_id']] = 1;
                if ($value['sub_order_id']) {
                    $new_sub_list[$value['sub_order_id']] = 1;
                }
            }
            //获取还款记录
            $get_bir_list = M("borrow_invest_repayment")->get_list(['invest_user_id' => $user_id, 'repay_status' => '已经还款'],
                'repay_time desc,id desc', 0, 100, '', 'repay_order_id');
            $new_bir_list = [];
            foreach ($get_bir_list as $value) {
                if (!$value['repay_order_id']) {
                    continue;
                }
                $new_bir_list[$value['repay_order_id']] = 1;
            }
            //获取提现记录
            $get_cash_list = M("cash")->get_list(['user_id' => $user_id], 'id desc', 0, 20, '', 'cash_order');
            $new_cash_list = [];
            foreach ($get_cash_list as $value) {
                if (!$value['cash_order']) {
                    continue;
                }
                $new_cash_list[$value['cash_order']] = 1;
            }
            foreach ($get_account_logs as $value) {
                switch ($value['type']) {
                    case "Recharge":
                        if (!isset($new_recharge_list[$value['order_id']])) {
                            return false;
                        }
                        break;
                    case "Invest":
                        if (!isset($new_invest_list[$value['order_id']])) {
                            return false;
                        }
                        break;
                    case "Loans":
                        if (!isset($new_sub_list[$value['order_id']])) {
//                            return false;
                        }
                        break;
                    case "Repayment":
                        if (!isset($new_bir_list[$value['order_id']])) {
                            return false;
                        }
                        break;
                    case "Cash":
                        if (!isset($new_cash_list[$value['order_id']])) {
                            return false;
                        }
                        break;
                    case "Yxb_Invest":
//                        if (!isset($new_cash_list[$value['order_id']])) {
//                            return false;
//                        }
                        break;
                    case "Yxb_Cash":
//                        if (!isset($new_cash_list[$value['order_id']])) {
//                            return false;
//                        }
                        break;
                    case "System":
                        break;
                    default:
                        return false;
                        break;
                }
            }
            return true;
        } else {
            return false;
        }
    }

}
