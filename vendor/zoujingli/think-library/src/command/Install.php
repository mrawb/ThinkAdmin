<?php

// +----------------------------------------------------------------------
// | Library for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2020 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: https://gitee.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 仓库地址 ：https://gitee.com/zoujingli/ThinkLibrary
// | github 仓库地址 ：https://github.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------

namespace think\admin\command;

use think\admin\Command;
use think\admin\service\ModuleService;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

/**
 * 插件更新安装指令
 * Class Install
 * @package think\admin\command
 */
class Install extends Command
{

    /**
     * 指定模块名称
     * @var string
     */
    protected $name;

    /**
     * 查询规则
     * @var array
     */
    protected $rules = [];

    /**
     * 忽略规则
     * @var array
     */
    protected $ignore = [];

    /**
     * 规则配置
     * @var array
     */
    protected $bind = [
        'admin'  => [
            'rules'  => ['think', 'app/admin'],
            'ignore' => [],
        ],
        'wechat' => [
            'rules'  => ['app/wechat'],
            'ignore' => [],
        ],
        'config' => [
            'rules'  => [
                'config/app.php',
                'config/cache.php',
                'config/log.php',
                'config/route.php',
                'config/session.php',
                'config/trace.php',
                'config/view.php',
                'public/index.php',
                'public/router.php',
            ],
            'ignore' => [],
        ],
        'static' => [
            'rules'  => [
                'public/static/plugs',
                'public/static/theme',
                'public/static/admin.js',
                'public/static/login.js',
            ],
            'ignore' => [],
        ],
    ];

    protected function configure()
    {
        $this->setName('xadmin:install');
        $this->addArgument('name', Argument::OPTIONAL, 'ModuleName', '');
        $this->setDescription("Source code Install and Update for ThinkAdmin");
    }

    protected function execute(Input $input, Output $output)
    {
        $this->name = trim($input->getArgument('name'));
        if (empty($this->name)) {
            $this->output->writeln('Module name of online installation cannot be empty');
        } elseif ($this->name === 'all') {
            foreach ($this->bind as $bind) {
                $this->rules = array_merge($this->rules, $bind['rules']);
                $this->ignore = array_merge($this->ignore, $bind['ignore']);
            }
            [$this->installFile(), $this->installData()];
        } elseif (isset($this->bind[$this->name])) {
            $this->rules = $this->bind[$this->name]['rules'] ?? [];
            $this->ignore = $this->bind[$this->name]['ignore'] ?? [];
            [$this->installFile(), $this->installData()];
        } else {
            $this->output->writeln("The specified module {$this->name} is not configured with installation rules");
        }
    }

    protected function installFile()
    {
        $data = ModuleService::instance()->grenerateDifference($this->rules, $this->ignore);
        if (empty($data)) $this->output->writeln('No need to update the file if the file comparison is consistent');
        else foreach ($data as $file) {
            [$state, $mode, $name] = ModuleService::instance()->updateFileByDownload($file);
            if ($state) {
                if ($mode === 'add') $this->output->writeln("--- {$name} add successfully");
                if ($mode === 'mod') $this->output->writeln("--- {$name} update successfully");
                if ($mode === 'del') $this->output->writeln("--- {$name} delete successfully");
            } else {
                if ($mode === 'add') $this->output->writeln("--- {$name} add failed");
                if ($mode === 'mod') $this->output->writeln("--- {$name} update failed");
                if ($mode === 'del') $this->output->writeln("--- {$name} delete failed");
            }
        }
    }

    protected function installData()
    {
    }

}